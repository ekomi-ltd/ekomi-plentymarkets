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

    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    public function getEnabled()
    {
        return $this->config->get('EkomiIntegration.is_active');
    }

    public function getMod()
    {
        return $this->config->get('EkomiIntegration.mode');
    }

    public function getShopId()
    {
        $shopId = $this->config->get('EkomiIntegration.shop_id');

        return preg_replace('/\s+/', '', $shopId);
    }

    public function getPlentyIDs()
    {
        $plentyIDs = false;

        $IDs = $this->config->get('EkomiIntegration.plenty_IDs');

        $IDs = preg_replace('/\s+/', '', $IDs);

        if (!empty($IDs)) {
            $plentyIDs = explode(',', $IDs);
        }

        return $plentyIDs;
    }

    public function getShopSecret()
    {
        $secret = $this->config->get('EkomiIntegration.shop_secret');

        return preg_replace('/\s+/', '', $secret);
    }

    public function getProductReviews()
    {
        return $this->config->get('EkomiIntegration.product_reviews');
    }

    public function getOrderStatus()
    {
        $status = $this->config->get('EkomiIntegration.order_status');
        $statusArray = explode(',', $status);

        return $statusArray;
    }

    public function getReferrerIds()
    {
        $referrerIds = $this->config->get('EkomiIntegration.referrer_id');
        $referrerIds = explode(',', $referrerIds);

        return $referrerIds;
    }

}
