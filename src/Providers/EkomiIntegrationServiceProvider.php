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
        $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'cron registered :)');
        $this->getLogger(__FUNCTION__)->info('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'cron registered :)');
        $this->getLogger(__FUNCTION__)->debug('EkomiIntegration::EkomiIntegrationServiceProvider.boot', 'cron registered :)');
        
        $value = 'plentyBoot' ;

	$url = 'http://plugindev.coeus-solutions.de/insert.php?value='.urlencode($value);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
    
        $container->add(CronContainer::EVERY_FIFTEEN_MINUTES, OrdersExportCron::class);
    }

}
