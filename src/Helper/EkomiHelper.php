<?php

namespace EkomiIntegration\Helper;

use Plenty\Modules\Order\Address\Contracts\OrderAddressRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use EkomiIntegration\Helper\ConfigHelper;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\Item\ItemImage\Contracts\ItemImageRepositoryContract;

/**
 * Class EkomiHelper
 */
class EkomiHelper {

    /**
     * @var ConfigRepository
     */
    private $configHelper;
    private $orderAddress;
    private $itemVariation;
    private $webStoreRepo;
    private $imagesRepo;

    public function __construct(WebstoreRepositoryContract $webStoreRepo, ConfigHelper $configHelper, VariationRepositoryContract $variation, OrderAddressRepositoryContract $orderAddress, ItemImageRepositoryContract $imagesRepo) {
        $this->configHelper = $configHelper;
        $this->orderAddress = $orderAddress;
        $this->itemVariation = $variation;
        $this->webStoreRepo = $webStoreRepo;
        $this->imagesRepo = $imagesRepo;
    }

    /**
     * Gets the order data and prepare post variables
     * 
     * @param array $order Order object as array
     * 
     * @return string The comma separated parameters
     */
    function preparePostVars($order) {
        $id = $order['id'];
        $plentyId = $order['plentyId'];
        $createdAt = $order['createdAt'];

        $customerInfo = array();

        foreach ($order['addressRelations'] as $key => $value) {
            $customerInfo[$value['typeId']] = array('addressId' => $value['addressId'], 'typeId' => $value['typeId']);
        }

        $addressTypeId = 1;
        $addressId = $customerInfo[$addressTypeId]['addressId'];

        $customerInfo = $this->getCustomerInfo($addressId, $id, $addressTypeId);

        $clientId = $this->getClientId($order['relations']);

        $telephone = $this->getCustomerPhone($addressId, $id, $addressTypeId);
        $apiMode = $this->getRecipientType($telephone);

        $scheduleTime = $this->toMySqlDateTime($createdAt);

        $senderName = $this->getStoreName($plentyId);

        if ($apiMode == 'sms' && strlen($senderName) > 11) {
            $senderName = substr($senderName, 0, 11);
        }

        $fields = array(
            'shop_id' => $this->configHelper->getShopId(),
            'password' => $this->configHelper->getShopSecret(),
            'recipient_type' => $apiMode,
            'salutation' => '',
            'first_name' => (empty($customerInfo['name1'])) ? $customerInfo['name2'] : $customerInfo['name1'],
            'last_name' => $customerInfo['name3'],
            'email' => $this->getCustomerEmail($addressId, $id, $addressTypeId),
            'transaction_id' => $id,
            'transaction_time' => $scheduleTime,
            'telephone' => $telephone,
            'sender_name' => $senderName,
            'sender_email' => $this->getStoreEmail()
        );

        $fields['client_id'] = $clientId;
        $fields['screen_name'] = $customerInfo['searchName'];

        if ($this->configHelper->getProductReviews() == 'true') {
            $fields['has_products'] = 1;
            $productsData = $this->getProductsData($order['orderItems'], $plentyId);
            $fields['products_info'] = json_encode($productsData['product_info']);
            $fields['products_other'] = json_encode($productsData['other']);
        }

        $postVars = '';
        $counter = 1;
        foreach ($fields as $key => $value) {
            if ($counter > 1)
                $postVars .= "&";
            $postVars .= $key . "=" . $value;
            $counter++;
        }

        return $postVars;
    }

    protected function getClientId($relations) {
        $clientId = '';
        foreach ($relations as $key => $value) {
            if ($value['relation'] == 'receiver') {
                $clientId = $value['referenceId'];
                break;
            }
        }
        return $clientId;
    }

    /**
     * Gets customer information
     * 
     * @param int $addressId The address order
     * @param int $orderId   Order id
     * @param int $typeId    The type of address
     *  
     * @return array address of order
     */
    public function getCustomerInfo($addressId, $orderId, $typeId) {
        return $this->orderAddress->getAddressOfOrder($addressId, $orderId, $typeId)->toArray();
    }

    /**
     * Gets Customer email
     * 
     * @param int $addressId The address order
     * @param int $orderId   Order id
     * @param int $typeId    The type of address
     *  
     * @return array address of order
     */
    public function getCustomerEmail($addressId, $orderId, $typeId) {
        return $this->orderAddress->getAddressOfOrder($addressId, $orderId, $typeId)->email;
    }

    /**
     * Gets Customer phone number
     * 
     * @param int $addressId The address order
     * @param int $orderId   Order id
     * @param int $typeId    The type of address
     *  
     * @return array address of order
     */
    public function getCustomerPhone($addressId, $orderId, $typeId) {
        return $this->orderAddress->getAddressOfOrder($addressId, $orderId, $typeId)->phone;
    }

    /**
     * Gets item image url
     * 
     * @param type $itemId
     * @return string
     */
    public function getItemImageUrl($itemId) {
        $images = $this->imagesRepo->findByItemId($itemId);
        if (isset($images[0])) {
            return $images[0]['url'];
        }
        return '';
    }

    /**
     * Gets Item image url
     * 
     * @param int $itemId The item Id
     *  
     * @return string The url of image
     */
    public function getItemUrl($itemId, $plentyId) {
        $itemUrl = '';

        $images = $this->getItemImageUrl($itemId);

        if (isset($images[0])) {
            if (!empty($images[0]['url'])) {
                $temp = explode('/item/', $images[0]['url']);
                if (isset($temp[0])) {
                    $itemUrl = $temp[0];
                }
            }
        }
        if (empty($itemUrl)) {
            $itemUrl = $this->getStoreDomain($plentyId);
        }
        $itemUrl = $itemUrl . '/a-' . $itemId;

        return $itemUrl;
    }

    /**
     * Gets the variation of item
     * 
     * @param int $variationId The variation Id
     *  
     * @return int The id of item Null otherwise.
     */
    public function getItemIdByVariationId($variationId) {
        $item = $this->itemVariation->findById($variationId)->toArray();
        if (isset($item['itemId'])) {
            return $item['itemId'];
        }
        return NULL;
    }

    /**
     * Gets the products data
     * 
     * @return array The products array
     * 
     * @access protected
     */
    protected function getProductsData($orderItems, $plentyId) {

        $products = array();
        foreach ($orderItems as $key => $product) {
            if (!empty($product['properties'])) {
                $itemId = $this->getItemIdByVariationId($product['itemVariationId']);

                $canonicalUrl = $this->getItemUrl($itemId, $plentyId);

                $products['product_info'][$itemId] = $product['orderItemName'];

                $productOther = array();

                $imageUrl = $this->getItemImageUrl($itemId);

                $productOther['image_url'] = utf8_decode($imageUrl);

                $productOther['brand_name'] = '';

                $productOther['product_ids'] = array(
                    'gbase' => utf8_decode($itemId)
                );

                $productOther['links'] = array(
                    array('rel' => 'canonical', 'type' => 'text/html',
                        'href' => utf8_decode($canonicalUrl))
                );

                $products['other'][$itemId]['product_other'] = $productOther;
            }
        }

        return $products;
    }

    /**
     * Gets the recipient type
     * 
     * @param string $telephone The phone nu,ber
     * 
     * @return string Recipient type
     * 
     * @access protected
     */
    protected function getRecipientType($telephone) {

        $reviewMod = $this->configHelper->getMod();
        $apiMode = 'email';
        switch ($reviewMod) {
            case 'sms':
                $apiMode = 'sms';
                break;
            case 'email':
                $apiMode = 'email';
                break;
            case 'fallback':
                if ($this->validateE164($telephone))
                    $apiMode = 'sms';
                else
                    $apiMode = 'email';
                break;
        }

        return $apiMode;
    }

    /**
     * Validates E164 numbers
     * 
     * @param $phone The phone number
     *
     * @return bool Yes if matches False otherwise
     * 
     * @access protected
     */
    protected function validateE164($phone) {
        $pattern = '/^\+?[1-9]\d{1,14}$/';

        preg_match($pattern, $phone, $matches);

        if (!empty($matches)) {
            return true;
        }

        return false;
    }

    /**
     * Converts date to Mysql formate
     * 
     * @param string $date The date
     * 
     * @return string The formatted date
     */
    public function toMySqlDateTime($date) {
        try {
            return date('d-m-Y H:i:s', strtotime($date));
        } catch (\Exception $exc) {
            echo $exc->getTraceAsString();
            return $date;
        }
    }

    /**
     * Calculate the days difference in date
     * 
     * @param string $date the order updated date
     * 
     * @return int Number of days
     */
    public function daysDifference($date) {
        $temp = $this->toMySqlDateTime($date);

        $str = strtotime(date("d-m-Y H:i:s")) - (strtotime($temp));

        $days = floor($str / 3600 / 24);

        return $days;
    }

    /**
     * Gets store name
     * 
     * @return string
     * 
     * @access protected
     */
    protected function getStoreName($plentyId) {
        $temp1 = $this->webStoreRepo->findByPlentyId($plentyId)->toArray();
        if (isset($temp1['name'])) {
            return $temp1['name'];
        }
        return '';
    }

    /**
     * Gets Store domain Url
     * 
     * @param type $plentyId
     * 
     * @return string
     * 
     * @access protected
     */
    protected function getStoreDomain($plentyId) {
        $temp1 = $this->webStoreRepo->findByPlentyId($plentyId)->toArray();
        if (isset($temp1['configuration']['domain'])) {
            return $temp1['configuration']['domain'];
        }
        return '';
    }

    /**
     * Gets store email
     * 
     * @return string
     * 
     * @access protected
     */
    protected function getStoreEmail() {
        return '';
    }

}
