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
        return $this->config->get('EkomiIntegration.shop_id');
    }

    public function getShopSecret() {
        return $this->config->get('EkomiIntegration.shop_secret');
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
