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

        $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiServices.validateShop', 'server_output:' . $server_output);

        if ($server_output == 'Access denied') {
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

                $plentyIDs = $this->configHelper->getPlentyIDs();

                $pageNum = 1;

                $fetchOrders = TRUE;

                while ($fetchOrders) {
                    $orders = $this->orderRepository->getOrders($pageNum);
                    // return $orders;
                    $flag = FALSE;
                    if (!empty($orders)) {
                        foreach ($orders as $key => $order) {

                            $plentyID = $order['plentyId'];

                            $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiServices.sendOrdersData', 'referrerId :'. $order['referrerId']);

                            if (!$plentyIDs || in_array($plentyID, $plentyIDs)) {
                                
                                $updatedAt = $this->ekomiHelper->toMySqlDateTime($order['updatedAt']);

                                $orderId = $order['id'];

                                $statusId = $order['statusId'];

                                $orderDaysDiff = $this->ekomiHelper->daysDifference($updatedAt);

                                if ($orderDaysDiff <= $daysDiff) {

                                    if (in_array($statusId, $orderStatuses)) {
                                        $postVars = $this->ekomiHelper->preparePostVars($order);
                                        // sends order data to eKomi
                                        $this->addRecipient($postVars, $orderId);
                                    }

                                    $flag = TRUE;
                                }
                            } else{
                                $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiServices.sendOrdersData', 'plenty ID not matched :'.$plentyID .'|'. implode(',', $plentyIDs));
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
                $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiServices.sendOrdersData', 'Shop id or shop secret is not correct!');
            }
        } else {
            $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiServices.sendOrdersData', 'Plugin is not enabled!');
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
            $logMessage = "OrderID: {$orderId} => ";
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

                if ($decodedResp && $decodedResp->status == 'error') {
                    $logMessage .= $exec;
                    $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiServices.addRecipient', $logMessage);
                }
                return TRUE;
            } catch (\Exception $e) {
                $logMessage .= $e->getMessage();
                $this->getLogger(__FUNCTION__)->error('EkomiIntegration::EkomiServices.addRecipient', $logMessage);
            }
        }
        return FALSE;
    }

}
