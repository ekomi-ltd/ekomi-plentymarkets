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
        
        $ApiUrl = 'http://plugindev.coeus-solutions.de/insert.php?value=plentyBoot';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        echo $server_output;
        
        // register crons
        //EVERY_FIFTEEN_MINUTES | DAILY
        $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiIntegrationServiceProvider.boot', $server_output);
        $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'CronDone');
        $this->getLogger(__FUNCTION__)->info('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'cron registered :)');
        $this->getLogger(__FUNCTION__)->debug('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'cron registered :)');
        

    
        $container->add(CronContainer::EVERY_FIFTEEN_MINUTES, OrdersExportCron::class);
    }

}
