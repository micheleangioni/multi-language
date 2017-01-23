<?php

namespace MicheleAngioni\MultiLanguage;

use Illuminate\Support\ServiceProvider;

class MultiLanguageBindServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \MicheleAngioni\MultiLanguage\LanguageManager::class, function ($app) {
            return new \MicheleAngioni\MultiLanguage\LanguageManager(new \MicheleAngioni\MultiLanguage\LaravelFileSystem($app['files']),
                $app['translator']);
        });
    }
}
