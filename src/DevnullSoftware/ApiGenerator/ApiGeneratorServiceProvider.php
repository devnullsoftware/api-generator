<?php namespace DevnullSoftware\ApiGenerator;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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
		$this->publishes([
			__DIR__.'/../../../public' => public_path('packages/devnullsoftware/api-generator'),
		], 'public');


        View::addNamespace('ApiGenerator', __DIR__.'/../../views');

		if (env('EXPOSE_APIS', true))
		{
			Route::get('/apis', 'DevnullSoftware\ApiGenerator\DocsController@apis2Welcome');
			Route::get('apis/data', 'DevnullSoftware\ApiGenerator\DocsController@apis');
			Route::get('/apis/{api}', 'DevnullSoftware\ApiGenerator\DocsController@apis2');

		}
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
