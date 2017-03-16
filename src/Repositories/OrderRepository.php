<?php

namespace EkomiIntegration\Repositories;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class OrderRepository
 */
class OrderRepository {

    public function __construct() {
        
    }

    /**
     * Gets order
     * 
     * @return array Return order
     */
    public function getOrders($pageNum = 1) {
        /** @var OrderRepositoryContract $orderRepo */
        $orderRepo = pluginApp(OrderRepositoryContract::class);

        if ($orderRepo instanceof OrderRepositoryContract) {

            /** @var PaginatedResult $paginatedResult */
            $paginatedResult = $orderRepo->searchOrders($pageNum, 50, $with = ['addresses', 'relation', 'reference']);

            if ($paginatedResult instanceof PaginatedResult) {
                if ($paginatedResult->getTotalCount() > 0) {
                    return $paginatedResult->getResult();
                }
            }
        }

        return NULL;
    }

}
