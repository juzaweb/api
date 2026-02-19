<?php

namespace Juzaweb\Modules\Api\Providers;

use Juzaweb\Modules\Core\Providers\ServiceProvider;
use Illuminate\Support\Facades\File;
use Juzaweb\Modules\Core\Facades\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\RequestGuard;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PassportUserProvider;
use Laravel\Passport\Guards\TokenGuard;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Contracts\Encryption\Encrypter;
use Juzaweb\Modules\Api\Models\ApiKey;

class ApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerMenus();

        Auth::extend('juzaweb', function ($app, $name, array $config) {
            $guard = new RequestGuard(function ($request) use ($app, $config) {
                // 1. Check for API Key
                $apiKey = $request->header('x-api-key');
                if ($apiKey) {
                    $keyModel = ApiKey::where('key', $apiKey)->first();

                    if ($keyModel && ! $keyModel->revoked) {
                        if ($keyModel->expires_at && $keyModel->expires_at->isPast()) {
                            return null;
                        }

                        $keyModel->update(['last_used_at' => now()]);
                        return $keyModel->user;
                    }

                    return null;
                }

                // 2. Fallback to Passport
                return (new TokenGuard(
                    $app->make(ResourceServer::class),
                    new PassportUserProvider(
                        Auth::createUserProvider($config['provider'] ?? null),
                        $config['provider'] ?? 'users'
                    ),
                    $app->make(ClientRepository::class),
                    $app->make(Encrypter::class),
                    $request
                ))->user($request);
            }, $app['request']);

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    public function register(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerMenus(): void
    {
        if (config('jw-api.enabled')) {
            Menu::make('api-keys', function () {
                return [
                    'title' => trans('api::app.api_keys'),
                    'icon' => 'fa fa-key',
                    'parent' => 'settings',
                    'position' => 'admin-top-profile',
                ];
            });
        }
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/jw-api.php' => config_path('jw-api.php'),
        ], 'api-config');
        $this->mergeConfigFrom(__DIR__ . '/../../config/jw-api.php', 'jw-api');
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'api');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/api');

        $sourcePath = __DIR__ . '/../resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'api-module-views']);

        $this->loadViewsFrom($sourcePath, 'api');
    }
}
