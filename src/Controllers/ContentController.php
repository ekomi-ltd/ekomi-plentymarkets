<?php

namespace EkomiIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use EkomiIntegration\Services\EkomiServices;

/**
 * Class ContentController
 * @package EkomiIntegration\Controllers
 */
class ContentController extends Controller {

    /**
     * @param Twig $twig
     * @return string
     */
    public function sendOrdersToEkomi(Twig $twig, EkomiServices $service): string {

        $service->sendOrdersData(7);

        return $twig->render('EkomiIntegration::content.hello');
    }

}
