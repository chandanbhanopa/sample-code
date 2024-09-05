<?php
/**
 * Helm
 *
 * Helper Class
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

namespace Helm\CastrolFund\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Helm\CastrolFund\Service\CastrolFundService;

/**
 * Handler
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Magento\Authorization\Model\UserContextInterface $userContext
     */
    protected $userContext;

    /**
     * Amin Config
     *
     * @var AdminConfiguration
     */
    private AdminConfiguration $adminConfig;

    /**
     * CastrolFundService $castrolFundService
     */
    protected CastrolFundService $castrolFundService;

    /**
     * Class constructor
     * @param Context $httpContext
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $httpContext,
        UserContextInterface $userContext,
        AdminConfiguration $adminConfig,
        CastrolFundService $castrolFundService
    ) {
        $this->httpContext = $httpContext;
        $this->userContext = $userContext;
        $this->adminConfig = $adminConfig;
        $this->castrolFundService = $castrolFundService;
    }

    /**
     * Get Customer Login
     * @return boolean
     */
    public function isCustomerLogIn()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Get Current Customer Group
     * @return integer
     */
    public function getCurrentCustomerGroup()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
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
        if ($this->adminConfig->isModuleEnabled()) {
            $data = $this->castrolFundService->getCastrolFundOrderData($order);
        }
        return $data;
    }
}
