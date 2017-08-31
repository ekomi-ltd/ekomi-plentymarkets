<?php

namespace EkomiIntegration\Helper;

use Plenty\Plugin\ConfigRepository;

/**
 * Class ConfigHelper
 */
class ConfigHelper {

    /**
     * @var ConfigRepository
     */
    private $config;

    public function __construct(ConfigRepository $config) {
        $this->config = $config;
    }

    public function getEnabled() {
        return $this->config->get('EkomiIntegration.is_active');
    }

    public function getMod() {
        return $this->config->get('EkomiIntegration.mode');
    }

    public function getShopId() {
        $shopId = $this->config->get('EkomiIntegration.shop_id');
        
        return preg_replace('/\s+/', '', $shopId);
    }

    public function getPlentyIDs() {
        $IDs = $this->config->get('EkomiIntegration.plenty_IDs');
        
        $IDs = preg_replace('/\s+/', '', $IDs);
        
        return explode(',',$IDs);
    }

    public function getShopSecret() {
        $secret = $this->config->get('EkomiIntegration.shop_secret');
        
        return preg_replace('/\s+/', '', $secret);
    }

    public function getProductReviews() {
        return $this->config->get('EkomiIntegration.product_reviews');
    }

    public function getOrderStatus() {
        $status = $this->config->get('EkomiIntegration.order_status');

        $statusArray = explode(',', $status);

        return $statusArray;
    }

}
