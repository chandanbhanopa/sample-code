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

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Helm\CastrolFund\Logger\CastrolFundLogger;

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

class OrderManagement
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
     * CastrolFundLogger $castrolFundLogger CartRepository
     */
    protected CastrolFundLogger $castrolFundLogger;

    /**
     * Constructor function
     *
     * @param CartRepositoryInterface $cartRepositoryInterface cartrepository
     * @param CastrolFundLogger       $castrolFundLogger       Castrol Fund logger
     */
    public function __construct(
        CartRepositoryInterface $cartRepositoryInterface,
        CastrolFundLogger $castrolFundLogger
    ) {
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->castrolFundLogger       = $castrolFundLogger;
    }

    /**
     * After Place order plugin method
     *
     * @param OrderManagementInterface $subject order managment interface
     * @param OrderInterface           $result  OrderInterface
     *
     * @return mixed
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result
    ) {
        $order = $result;
        $quoteId = $order->getQuoteId();
        $orderId = $result->getIncrementId();
        if ($orderId) {
            $quote = $this->cartRepositoryInterface->get($quoteId);
        }
        return $result;
    }
}
