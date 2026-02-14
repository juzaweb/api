<?php

namespace Juzaweb\Modules\Api\Providers;

use Juzaweb\Modules\Core\Providers\ServiceProvider;
use Illuminate\Support\Facades\File;
use Juzaweb\Modules\Core\Facades\Menu;

class ApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerMenus();
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
