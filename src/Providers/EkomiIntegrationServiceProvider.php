<?php

namespace EkomiIntegration\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use EkomiIntegration\Crons\OrdersExportCron;
use Plenty\Plugin\Log\Loggable;
/**
 * Class EkomiIntegrationServiceProvider
 * @package EkomiIntegration\Providers
 */
class EkomiIntegrationServiceProvider extends ServiceProvider {
    
    use Loggable;
    
    /**
     * Register the service provider.
     */
    public function register() {
        $this->getApplication()->register(EkomiIntegrationRouteServiceProvider::class);
    }

    public function boot(CronContainer $container) {
        // register crons
        //EVERY_FIFTEEN_MINUTES | DAILY
        
        //$this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'CronRegistered');
        $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'CronRegistered');
        $this->getLogger(__FUNCTION__)->info('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'CronRegistered');

        $container->add(CronContainer::DAILY, OrdersExportCron::class);
    }

}
