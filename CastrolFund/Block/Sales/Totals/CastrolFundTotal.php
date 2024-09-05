<?php
/**
 * Helm
 *
 * Castrol Funds
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

namespace Helm\CastrolFund\Block\Sales\Totals;

use Helm\CastrolFund\ViewModel\CastrolFund;
use Magento\Framework\View\Element\Template\Context;

/**
 * CastrolFund
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class CastrolFundTotal extends \Magento\Framework\View\Element\Template
{
    /**
     * Castrol fund
     *
     * @var CastrolFund
     */
    protected $castrolFundHelper;

    /**
     * Hold the order data
     *
     * @var Order
     */
    protected $order;

    /**
     * Data object
     *
     * @var \Magento\Framework\DataObject
     */
    protected $source;

    /**
     * Constructor Function
     *
     * @param Context     $context           context
     * @param CastrolFund $castrolFundHelper helper function
     * @param array       $data              data
     */
    public function __construct(
        Context $context,
        CastrolFund $castrolFundHelper,
        array $data = []
    ) {
        $this->castrolFundHelper = $castrolFundHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get Store
     *
     * @return object store
     */
    public function getStore()
    {
        return $this->order->getStore();
    }

    /**
     * Get Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get Label Properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get Value Properties Function
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * InitTotal Function
     *
     * @return void
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->order = $parent->getOrder();
        $this->source = $parent->getSource();
        
        if ($this->source->getUsedCastrolFundAmount()) {
            $castrolFunds = new \Magento\Framework\DataObject(
                [
                    'code' => 'castrol_funds',
                    'strong' => false,
                    'value' => -$this->source->getUsedCastrolFundAmount(),
                    'label' => $this->castrolFundHelper->getCastrolFundLabel(),
                ]
            );
    
            $parent->addTotal($castrolFunds, 'castrol_funds');
        }
        return $this;
    }
}
