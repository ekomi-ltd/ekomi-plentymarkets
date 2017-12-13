<?php

namespace EkomiIntegration\Repositories;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;
use Plenty\Plugin\Log\Loggable;
use Plenty\Modules\Authorization\Services\AuthHelper;
/**
 * Class OrderRepository
 */
class OrderRepository {
	
	use Loggable;
	
    public function __construct() {
        
    }

    /**
     * Gets order
     * 
     * @return array Return order
     */
    public function getOrders($pageNum = 1) {
        
	    $orderRepo = pluginApp(OrderRepositoryContract::class);

        if ($orderRepo instanceof OrderRepositoryContract) {

            /** @var PaginatedResult $paginatedResult */
            $paginatedResult = $orderRepo->searchOrders($pageNum, 200, $with = ['addresses', 'relation', 'reference']);

            if ($paginatedResult instanceof PaginatedResult) {
                if ($paginatedResult->getTotalCount() > 0) {
                    return $paginatedResult->getResult();
                }
            }
        }

        return array();
    }

}
