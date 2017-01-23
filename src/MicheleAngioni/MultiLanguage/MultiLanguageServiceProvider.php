<?php

namespace MicheleAngioni\MultiLanguage;

use Illuminate\Support\ServiceProvider;

class MultiLanguageServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('ma_multilanguage.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php', 'ma_multilanguage'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}
