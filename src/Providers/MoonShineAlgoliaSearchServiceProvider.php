<?php

declare(strict_types=1);

namespace Leeto\MoonShineAlgoliaSearch\Providers;

use Illuminate\Support\ServiceProvider;
use Leeto\MoonShineAlgoliaSearch\Commands\AlgoliaSearchIndexes;

final class MoonShineAlgoliaSearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'algolia-search');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'algolia-search');

        $this->publishes([
            __DIR__ . '/../../config/algolia.php' => config_path('algolia.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/algolia.php',
            'algolia-search'
        );

        $this->publishes([
            __DIR__ . '/../../lang' => $this->app->langPath('vendor/algolia-search'),
        ]);

        $this->commands([
            AlgoliaSearchIndexes::class
        ]);
    }
}
