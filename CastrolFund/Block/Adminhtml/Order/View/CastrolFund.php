<?php
/**
 * Helm
 *
 * Castrol Funds For Aminhtml
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

namespace Helm\CastrolFund\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Phrase;
use Magento\Sales\Api\OrderRepositoryInterface;
use Helm\CastrolFund\Service\CastrolFundService;

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
class CastrolFund extends Template
{
    /**
     * Order Repository Interface
     *
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * CastrolFundService variable
     *
     * @var CastrolFundService $castrolFundService
     */
    protected $castrolFundService;

    /**
     * Constructor Function
     *
     * @param Template\Context         $context         Template context
     * @param OrderRepositoryInterface $orderRepository OrderRepository
     * @param array                    $data            Data helper
     * @param JsonHelper|null          $jsonHelper      Json data
     * @param DirectoryHelper|null     $directoryHelper Directory Helper
     */
    public function __construct(
        Template\Context $context,
        OrderRepositoryInterface $orderRepository,
        CastrolFundService $castrolFundService,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->orderRepository = $orderRepository;
        $this->castrolFundService = $castrolFundService;
    }

    /**
     * Phrase
     *
     * @return Phrase
     */
    public function getCastrolFundValue()
    {
        $castrolFund = 0;
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        if ((int)$order->getData('used_castrol_fund_amount')) {
            $castrolFund = $order->getData('used_castrol_fund_amount');
        }

        return $castrolFund;
    }

    /**
     * GetCastrolFundForAdmin function
     *
     * @return void
     */
    public function getCastrolFundForAdmin()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        return $this->castrolFundService->getCastrolFundOrderData($order);
    }
}
