<?php
/**
 * Helm
 *
 * Override the Order success email template
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

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Helm\CastrolFund\Model\Config\AdminConfiguration;

/**
 * AddCustomVariableToEmail Observer
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

class AddCustomVariableToEmail implements ObserverInterface
{

    /**
     * AdminConfiguration variable
     *
     * @var AdminConfiguration $adminConfiguration
     */

    protected $adminConfiguration;
    /**
     * Constructor function
     *
     * @param AdminConfiguration $adminConfiguration admin configuratino
     */
    public function __construct(AdminConfiguration $adminConfiguration)
    {
        $this->adminConfiguration = $adminConfiguration;
    }

    /**
     * Execute Function
     *
     * @param \Magento\Framework\Event\Observer $observer event observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (! $this->adminConfiguration->isModuleEnabled()) {
            return $this;
        }
        $transport = $observer->getTransport();
        $order = $transport['order'];
        if (!$order->getData("is_applied_castrol_fund")) {
            return $this;
        }
        $transport['is_used_castrol_fund'] = $order->getData("is_applied_castrol_fund") ? 1 : 0;
        $transport['castrol_fund'] = "$".round($order->getData("used_castrol_fund_amount"), 2);
    }
}
