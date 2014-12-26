<?php

namespace TopGames\MultiLanguage;

use Illuminate\Support\ServiceProvider;

class MultiLanguageBindServiceProvider extends ServiceProvider {

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
            'TopGames\MultiLanguage\LanguageManager', function($app)
        {
            return new \TopGames\MultiLanguage\LanguageManager(new \TopGames\MultiLanguage\LaravelFileSystem($app['files']), $app['translator']);
        });
	}

}
