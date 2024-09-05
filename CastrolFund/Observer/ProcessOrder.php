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

 namespace Helm\CastrolFund\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Helm\CastrolFund\Logger\CastrolFundLogger;
use Helm\CastrolFund\Service\CastrolFundService;

/**
 * Order Managment
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */


class ProcessOrder implements ObserverInterface
{
   
    /**
     * CastrolFundLogger object
     *
     * CastrolFundLogger $castrolFundLogger CartRepository
     */
    protected CastrolFundLogger $castrolFundLogger;

    /**
     * Object copy service
     *
     * @var CastrolFundService $castrolFundService object
     */
    protected CastrolFundService $castrolFundService;

    /**
     * Constructor function
     *
     * @param CastrolFundLogger  $castrolFundLogger  Castrol Fund logger
     * @param CastrolFundService $castrolFundService Service class
     */
    public function __construct(
        CastrolFundLogger $castrolFundLogger,
        CastrolFundService $castrolFundService
    ) {
        $this->castrolFundLogger       = $castrolFundLogger;
        $this->castrolFundService       = $castrolFundService;
    }

    /**
     * Undocumented function
     *
     * @param Observer $observer observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        $isCastrolFundApplied = $quote->getData('is_applied_castrol_fund');
        $useCastrolFund = $quote->getData('used_castrol_fund_amount');
        /* @var Order $order */
        $order = $observer->getOrder();
        $territoryData = $this->castrolFundService->getCustomerTerritoryData();
        $order->setData('is_applied_castrol_fund', $isCastrolFundApplied);
        $order->setData('used_castrol_fund_amount', $useCastrolFund);
        $order->setData('territory_number', $territoryData['territory_number']);
        $order->setData('parent_territory_number', $territoryData['parent_territory_number']);
        $order->setData('gl_number', $territoryData['gl_number']);
        $order->save();
        $remainingAmount = $quote->getData('available_castrol_fund_amount') - $useCastrolFund;
        $quote->setData('available_castrol_fund_amount', $remainingAmount);
        //$this->castrolFundService->updateCustomerInitialBalance($remainingAmount);
    }
}
