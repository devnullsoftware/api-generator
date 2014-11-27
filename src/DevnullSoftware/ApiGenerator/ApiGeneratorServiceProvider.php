<?php namespace DevnullSoftware\ApiGenerator;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;


class ApiGeneratorServiceProvider extends ServiceProvider {

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
		$this->package('devnullsoftware/api-generator', 'ApiGenerator');

        Route::get('/apis', 'DevnullSoftware\ApiGenerator\DocsController@apis');
        Route::get('/apis/route-models', 'DevnullSoftware\ApiGenerator\DocsController@routeModels');
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
		return array();
	}

}
