<?php
/**
 * Helm
 *
 * Perform Operation after cancel order
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

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Helm\CastrolFund\Logger\CastrolFundLogger;
use Magento\Customer\API\CustomerRepositoryInterface;
use Helm\CastrolFund\Model\Config\AdminConfiguration;

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

class UpdateCastrolFunds
{

    /**
     * CartRepositoryInterface object
     *
     * CartRepositoryInterface $cartRepositoryInterface CartRepository
     */
    protected CartRepositoryInterface $cartRepositoryInterface;

    /**
     * CastrolFundLogger object
     *
     * CastrolFundLogger $castrolFundLogger castrolFundLogger
     */
    protected CastrolFundLogger $castrolFundLogger;

    /**
     * OrderRepositoryInterface object
     *
     * OrderRepositoryInterface $orderInterface OrderRepository
     */
    protected OrderRepositoryInterface $orderInterface;

    /**
     * @var Magento\Customer\API\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Helm\CastrolFund\Model\Config\AdminConfiguration
     */
    protected $adminConfiguration;

    /**
     * Constructor function
     *
     * @param CartRepositoryInterface     $cartRepositoryInterface cartrepository
     * @param CastrolFundLogger           $castrolFundLogger       Castrol Fund logger
     * @param OrderRepositoryInterface    $orderInterface          Order Interface
     * @param CustomerRepositoryInterface $customerRepository      Customer Repository
     * @param AdminConfiguration          $adminConfiguration      Admin Configuration
     */
    public function __construct(
        CartRepositoryInterface $cartRepositoryInterface,
        CastrolFundLogger $castrolFundLogger,
        OrderRepositoryInterface $orderInterface,
        CustomerRepositoryInterface $customerRepository,
        AdminConfiguration $adminConfiguration,
    ) {
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->castrolFundLogger       = $castrolFundLogger;
        $this->orderInterface          = $orderInterface;
        $this->customerRepository      = $customerRepository;
        $this->adminConfiguration      = $adminConfiguration;
    }

    /**
     * After Place order plugin method
     *
     * @param OrderManagementInterface $subject order managment interface
     * @param OrderInterface           $result  OrderInterface
     *
     * @return mixed
     */
    public function beforeCancel(
        OrderManagementInterface $subject,
        int $id
    ) {
        
        $order = $this->orderInterface->get($id);
        $customerId = $order->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $customerBalance = $customer->getCustomAttribute('initial_balance')->getValue();
        // if ($order->getIsAppliedCastrolFund()) {
        //     // $usedCastrolFundsAmount = $order->getUsedCastrolFundAmount();
        //     // $customerBalance = $customerBalance + $usedCastrolFundsAmount;
        //     // $customer->setCustomAttribute('initial_balance', $customerBalance);
        //     // $this->customerRepository->save($customer);
        // }
        return $id;
    }
}
