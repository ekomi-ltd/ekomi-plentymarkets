<?php

namespace EkomiIntegration\Services;

use EkomiIntegration\Helper\EkomiHelper;
use EkomiIntegration\Helper\ConfigHelper;
use EkomiIntegration\Repositories\OrderRepository;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

/**
 * Class EkomiServices
 */
class EkomiServices {

    use Loggable;

    /**
     * @var ConfigRepository
     */
    private $configHelper;
    private $ekomiHelper;
    private $orderRepository;

    public function __construct(ConfigHelper $configHelper, OrderRepository $orderRepo, EkomiHelper $ekomiHelper) {
        $this->configHelper = $configHelper;
        $this->ekomiHelper = $ekomiHelper;
        $this->orderRepository = $orderRepo;
    }

    /**
     * Validates the shop
     * 
     * @return boolean True if validated False otherwise
     */
    public function validateShop() {
        $ApiUrl = 'http://api.ekomi.de/v3/getSettings';

        $ApiUrl .= "?auth={$this->configHelper->getShopId()}|{$this->configHelper->getShopSecret()}";
        $ApiUrl .= '&version=cust-1.0.0&type=request&charset=iso';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        if ($server_output == 'Access denied') {
            $this->getLogger(__FUNCTION__)->error('invalid credentials', "url:{$ApiUrl}");
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Sends orders data to eKomi System
     */
    public function sendOrdersData($daysDiff = 7) {
    	
        if ($this->configHelper->getEnabled() == 'true') {
            if ($this->validateShop()) {

                $orderStatuses = $this->configHelper->getOrderStatus();
                $referrerIds   = $this->configHelper->getReferrerIds();
                $plentyIDs     = $this->configHelper->getPlentyIDs();

                $pageNum = 1;

                $fetchOrders = TRUE;
				
                while ($fetchOrders) {
                    $orders = $this->orderRepository->getOrders($pageNum);
                    $this->getLogger(__FUNCTION__)->error('orders-chunk', 'count:'.count($orders));
                    // return $orders;
                    $flag = FALSE;
                    if ($orders && !empty($orders)) {
                        foreach ($orders as $key => $order) {
                            //$this->getLogger(__FUNCTION__)->error('order', $order);
                            $orderId = $order['id'];
                            $plentyID   = $order['plentyId'];
                            $referrerId = $order['orderItems'][0]['referrerId'];

                            if (!$plentyIDs || in_array($plentyID, $plentyIDs)) {

                                if (!empty($referrerIds) && in_array((string)$referrerId, $referrerIds)) {
                                    $this->getLogger(__FUNCTION__)->error(
                                        "OrderID:{$orderId} ,referrerID:{$referrerId}|Blocked",
                                        'OrderID:' . $orderId .
                                        '|ReferrerID:' . $referrerId .
                                        ' Blocked in plugin configuration.'
                                    );
                                    $flag = TRUE;
                                    continue;
                                }

                                $updatedAt = $this->ekomiHelper->toMySqlDateTime($order['updatedAt']);

                                $statusId = $order['statusId'];

                                $orderDaysDiff = $this->ekomiHelper->daysDifference($updatedAt);

                                if ($orderDaysDiff <= $daysDiff) {

                                    if (in_array($statusId, $orderStatuses)) {

                                        $postVars = $this->ekomiHelper->preparePostVars($order);
                                        // sends order data to eKomi
                                        $this->addRecipient($postVars, $orderId);
                                    }

                                    $flag = TRUE;
                                } else{
                                    $this->getLogger(__FUNCTION__)->error("orderId:{$orderId}|old", "orderId:{$orderId}|days difference failed $orderDaysDiff <= $daysDiff");
                                }

                            } else{
                                $this->getLogger(__FUNCTION__)->error('PlentyID not matched', 'plentyID('.$plentyID .') not matched with PlentyIDs:'. implode(',', $plentyIDs));
                            }
                        }
                    }
                    //check to fetch next page
                    if ($flag) {
                        $fetchOrders = TRUE;
                        $pageNum++;
                    } else {
                        $fetchOrders = FALSE;
                    }
                }
            } else {
                $this->getLogger(__FUNCTION__)->error('invalid credentials', "shopId:{$this->configHelper->getShopId()},shopSecret:{$this->configHelper->getShopSecret()}");
            }
        } else {
            $this->getLogger(__FUNCTION__)->error('Plugin not active', 'is_active:'.$this->configHelper->getEnabled());
        }
    }

    /**
     * Calls the addRecipient API
     * 
     * @param string $postVars
     * 
     * @return string return the api status
     */
    public function addRecipient($postVars, $orderId = '') {
        if ($postVars != '') {
            $logMessage = "OrderID:{$orderId}";
            /*
             * The Api Url
             */
            $apiUrl = 'https://srr.ekomi.com/add-recipient';

            $boundary = md5('' . time());
            /*
             * Send the curl call
             */
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('ContentType:multipart/form-data;boundary=' . $boundary));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postVars);
                $exec = curl_exec($ch);
                curl_close($ch);

                $decodedResp = json_decode($exec);

               //if ($decodedResp && $decodedResp->status == 'error') {
                    $this->getLogger(__FUNCTION__)->error("$logMessage|$decodedResp->status", $logMessage .= $exec);
                //}
                return TRUE;
            } catch (\Exception $e) {
                $this->getLogger(__FUNCTION__)->error("$logMessage|exception", $logMessage .= $e->getMessage());
            }
        }
        return FALSE;
    }

}
