<?php
namespace EkomiIntegration\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

/**
 * Class EkomiIntegrationRouteServiceProvider
 * @package EkomiIntegration\Providers
 */
class EkomiIntegrationRouteServiceProvider extends RouteServiceProvider
{
	/**
	 * @param Router $router
	 */
	public function map(Router $router)
	{
		$router->get('sendOrdersToEkomi', 'EkomiIntegration\Controllers\ContentController@sendOrdersToEkomi');
	}

}
