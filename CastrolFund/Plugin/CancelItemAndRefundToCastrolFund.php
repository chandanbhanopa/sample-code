<?php
/**
 * Helm
 *
 * Perform Operation after place order
 *
 *
 * PHP version 8.2
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

namespace Helm\CastrolFund\Plugin;

use Helm\CastrolFund\Logger\CastrolFundLogger;
use Alpine\OrderActions\Controller\Adminhtml\Item\Cancel;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\API\CustomerRepositoryInterface;

/**
 * Order Managment Plugin
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

class CancelItemAndRefundToCastrolFund
{

    /**
     * CartRepositoryInterface object
     *
     * CartRepositoryInterface $cartRepositoryInterface CartRepository
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * CastrolFundLogger object
     *
     * CastrolFundLogger $castrolFundLogger CartRepository
     */
    protected CastrolFundLogger $castrolFundLogger;

    /**
     * @var Magento\Customer\API\CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * Constructor function
     *
     * @param CartRepositoryInterface $orderRepository cartrepository
     * @param CastrolFundLogger       $castrolFundLogger       Castrol Fund logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CastrolFundLogger $castrolFundLogger,
        CustomerRepositoryInterface $customerRepository,
    ) {
        $this->orderRepository    = $orderRepository;
        $this->castrolFundLogger  = $castrolFundLogger;
        $this->customerRepository = $customerRepository;
    }

    /**
     * After Cancel Order Item
     *
     * @param OrderManagementInterface $subject order managment interface
     * @param OrderInterface           $result  OrderInterface
     *
     * @return mixed
     */
    public function afterExecute(
        Cancel $subject,
        $result
    ) {
        
        $itemId  = $subject->getRequest()->getParam('item_id');
        $orderId = $subject->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $itemAmount = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getItemId() == $itemId) {
                $itemAmount += $item->getRowTotal();
            }
          
        }

        $customerId = $order->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $customerBalance = $customer->getCustomAttribute('initial_balance')->getValue();
        if ($order->getIsAppliedCastrolFund()) {
            
            $customerBalance = $customerBalance + $itemAmount;
            $customer->setCustomAttribute('initial_balance', $customerBalance);
            $this->customerRepository->save($customer);
            $salesOrderCastrolFunds = $order->getUsedCastrolFundAmount();
            $order->setUsedCastrolFundAmount($salesOrderCastrolFunds - $itemAmount);
            $this->orderRepository->save($order);
        }
        return $result;
    }
}
