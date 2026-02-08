<?php

namespace Juzaweb\Modules\Api\Providers;

use Juzaweb\Modules\Core\Providers\ServiceProvider;
use Illuminate\Support\Facades\File;

class ApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //

        $this->booted(
            function () {
                $this->registerMenus();
            }
        );
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
        if (File::missing(storage_path('app/installed'))) {
            return;
        }

        //
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('api.php'),
        ], 'api-config');
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'api');
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'api');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/api');

        $sourcePath = __DIR__ . '/../src/resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'api-module-views']);

        $this->loadViewsFrom($sourcePath, 'api');
    }
}
