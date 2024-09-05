<?php
/**
 * Helm
 *
 * CastrolFund Interface
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
declare(strict_types=1);

namespace Helm\CastrolFund\Model\Total\Quote;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Helm\CastrolFund\Service\CastrolFundService;
use Helm\CastrolFund\Logger\CastrolFundLogger;

/**
 * Collect
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

class Collect extends AbstractTotal
{
    /**
     * Admin Configuration class
     *
     * @var AdminConfiguration $adminConfiguration
     */
    protected AdminConfiguration $adminConfiguration;

    /**
     * Admin Configuration class
     *
     * @var AdminConfiguration $adminConfiguration
     */
    protected CastrolFundService $castrolFundService;

    /**
     * Admin Configuration class
     *
     * @var AdminConfiguration $adminConfiguration
     */
    protected CastrolFundLogger $castrolFundLogger;

    /**
     * Constructor Function
     *
     * @param AdminConfiguration $adminConfiguration admin configuration
     * @param CastrolFundService $castrolFundService fund services
     * @param CastrolFundLogger  $castrolFundLogger  castrol fund logger
     */
    public function __construct(
        AdminConfiguration $adminConfiguration,
        CastrolFundService $castrolFundService,
        CastrolFundLogger $castrolFundLogger
    ) {
        $this->adminConfiguration = $adminConfiguration;
        $this->castrolFundService = $castrolFundService;
        $this->castrolFundLogger = $castrolFundLogger;
    }

    /**
     * Collect Function
     *
     * @param Quote $quote quote object
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total total amount
     *
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): Collect {
        parent::collect($quote, $shippingAssignment, $total);
       
            $availableCastrolFund = (float)$this->castrolFundService->getCustomerInitialBalance();
            $isCastrolFundApplied = (int) $quote->getData('is_applied_castrol_fund');
            $this->castrolFundLogger->info(" Collect Available Balance: ".$availableCastrolFund);
            $this->castrolFundLogger->info(" Collect Is Applied : ".$isCastrolFundApplied);

        if ($availableCastrolFund && $isCastrolFundApplied) {
            $grandTotal = (float) $total->getGrandTotal();

            //Case : 1
            if ($availableCastrolFund > $grandTotal) {
                $discountAmount = $grandTotal;
            }

            //Case : 2
            if ($availableCastrolFund < $grandTotal) {
                $discountAmount = $availableCastrolFund;
            }

            //Case : 3
            if ($availableCastrolFund == $grandTotal) {
                $discountAmount = $grandTotal;
            }

            $calcTotal = $grandTotal - $discountAmount;
            $quote->setGrandTotal($calcTotal);
            $total->setGrandTotal($calcTotal);
            $total->setBaseGrandTotal($calcTotal);
            $this->castrolFundLogger->info("Collect Grand Amount: ".$grandTotal);
            $this->castrolFundLogger->info("Collect Discount Amount: ".$discountAmount);
            $quote->setCustomDiscount(-$discountAmount);
        }
        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param Quote $quote
     * @param Total $total
     *
     * @return array
     */
    public function fetch(
        Quote $quote,
        Total $total
    ): array {

            $fetch = parent::fetch($quote, $total);
            $orderTotalAmount = $total->getShippingAmount() + $total->getSubtotal();
        if ($total->getSubtotalWithDiscount()) {
            $orderTotalAmount = $total->getSubtotalWithDiscount();
        }
            
            $discountAmount = 0;
            $this->castrolFundLogger->info("-------------Fetch Method Start-----------");
        
            $isCastrolFundApplied = (int) $quote->getData('is_applied_castrol_fund');
            $availableCastrolFund = (float) $quote->getData('available_castrol_fund_amount');

            $this->castrolFundLogger->info("Fetch Quote Grand Total: ". $quote->getGrandTotal());
            $grandTotal = (float) $quote->getGrandTotal();
            $this->castrolFundLogger->info("Fetch Total Grand Total:". $total->getGrandTotal());
            $finalAmount = $orderTotalAmount + $total->getShippingAmount();
            //Case : 1
        if ($availableCastrolFund > $finalAmount) {
            $discountAmount = $finalAmount;
        }
            //Case : 2
        
        if ((float) $availableCastrolFund < (float)$finalAmount) {
            $discountAmount = $availableCastrolFund;
        }

            //Case : 3
        if ($availableCastrolFund == $finalAmount) {
            $discountAmount = $finalAmount;
            
        }
        
        $this->castrolFundLogger->info(" Fetch Is isCastrolFundApplied : ".$isCastrolFundApplied);
        $this->castrolFundLogger->info(" Fetch Grand Amount: ".$grandTotal);
        $this->castrolFundLogger->info(" Fetch Discount Amount: ".$discountAmount);

            $fetch = [
                'code' => 'castrol_fund_totals',
                'title' => $this->adminConfiguration->getCastrolFundLabel(
                    $quote->getStoreId()
                ),
                'value' => $isCastrolFundApplied ? -(float) $discountAmount : 0
            ];

            $this->castrolFundLogger->info("-------------Fetch Method End-----------");
            return $fetch;
    }
}
