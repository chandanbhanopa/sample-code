<?php
/**
 * Helm
 *
 * Info Class
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

namespace Helm\CastrolFund\Block\Order;

use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Helm\CastrolFund\Service\CastrolFundService;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Block\Order\Info as SalesInfo;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;

/**
 * Order Info class
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

class Info extends SalesInfo
{
    
    /**
     * CastrolFundService $castrolFundService
     */
    protected CastrolFundService $castrolFundService;

    /**
     * Configuration
     *
     * @var AdminConfiguration $adminConfiguration
     */
    protected AdminConfiguration $adminConfiguration;

    /**
     * Construct
     *
     * @param TemplateContext    $context            template context
     * @param Registry           $registry           registery
     * @param PaymentHelper      $paymentHelper      payment helper
     * @param AddressRenderer    $addressRenderer    address renderer
     * @param CastrolFundService $castrolFundService castrol fund service
     * @param AdminConfiguration $adminConfiguration admin configuration
     * @param array              $data               Data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        AddressRenderer $addressRenderer,
        CastrolFundService $castrolFundService,
        AdminConfiguration $adminConfiguration,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $paymentHelper,
            $addressRenderer,
            $data
        );
        $this->castrolFundService = $castrolFundService;
        $this->adminConfiguration = $adminConfiguration;
    }

    /**
     * GetCastrolFundOrderData Function
     *
     * @param mixed $order Order data
     *
     * @return array
     */
    public function getCastrolFundOrderData($order): array
    {
        $data = [];
        if ($this->adminConfiguration->isModuleEnabled()) {
            $data = $this->castrolFundService->getCastrolFundOrderData($order);
        }
        return $data;
    }
}
