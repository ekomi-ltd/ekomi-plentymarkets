<?php

namespace EkomiIntegration\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use EkomiIntegration\Crons\OrdersExportCron;

/**
 * Class EkomiIntegrationServiceProvider
 * @package EkomiIntegration\Providers
 */
class EkomiIntegrationServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     */
    public function register() {
        $this->getApplication()->register(EkomiIntegrationRouteServiceProvider::class);
    }

    public function boot(CronContainer $container) {
        // register crons
        //EVERY_FIFTEEN_MINUTES | DAILY
        $container->add(CronContainer::DAILY, OrdersExportCron::class);
    }

}
