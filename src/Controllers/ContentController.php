<?php

namespace EkomiIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use EkomiIntegration\Services\EkomiServices;

 use Plenty\Plugin\Http\Request;
 use Plenty\Plugin\Log\Loggable;
/**
 * Class ContentController
 * @package EkomiIntegration\Controllers
 */
class ContentController extends Controller {
  use Loggable;
    /**
     * @param Twig $twig
     * @return string
     */
    public function sendOrdersToEkomi(Twig $twig, EkomiServices $service): string {
        $this->getLogger(__FUNCTION__)->error('EkomiIntegration::ContentController.sendOrdersToEkomi', 'routeHiited');
        //$service->sendOrdersData(7);
        echo 'test :P)';
        return $twig->render('EkomiIntegration::content.hello');
    }

}
