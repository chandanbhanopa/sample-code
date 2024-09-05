<?php
/**
 * Helm
 *
 * Class to get the system config value
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

namespace Helm\CastrolFund\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context;

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
class AdminConfiguration
{
    private const MODULE_ENABLE = 'castrol_fund/general/enable';
    private const ALLOWED_CUSTOMER_GROUPS = 'castrol_fund/general/allowed_customer_groups';
    private const SANDBOX_ENABLE = 'castrol_fund/sandbox/enable';
    private const SANDBOX_AMOUNT = 'castrol_fund/sandbox/value';
    private const CASTROL_FUND_LABEL = 'castrol_fund/checkout/castrol_fund_label';
    private const CASTROL_FUND_ORDER_MESSAGE = 'castrol_fund/checkout/castrol_fund_order_massage';
    private const CASTROL_FUND_HEADER_TITLE = 'castrol_fund/checkout/castrol_header_label';

    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Http Context
     *
     * @var Context
     */
    private $httpContext;
    /**
     * Class constructor
     *
     * @param ScopeConfigInterface $scopeConfig parameters
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HttpContext $httpContext
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->httpContext = $httpContext;
    }

    /**
     * Checks is module enabled
     *
     * @param null|int|string $store mixed type
     *
     * @return bool
     */
    public function isModuleEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::MODULE_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns array of the allowed to use coop
     * funds customer groups
     *
     * @param null|int|string $store Mixed
     *
     * @return array
     */
    public function getAllowedGroups($store = null): array
    {
        $allowedGroupsConfigData = $this->scopeConfig->getValue(
            self::ALLOWED_CUSTOMER_GROUPS,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        $allowedGroups = [];

        if ($allowedGroupsConfigData) {
            $allowedGroups = explode(',', $allowedGroupsConfigData);
        }

        return $allowedGroups;
    }
    /**
     * Checks is sandbox enabled
     *
     * @param null|int|string $store Mixed Params
     *
     * @return bool
     */
    public function isSandboxEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::SANDBOX_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns sandbox coop fund amount
     *
     * @param null|int|string $store Mixed Params
     *
     * @return float
     */
    public function getSandboxAmount($store = null): float
    {
        return (float) $this->scopeConfig->getValue(
            self::SANDBOX_AMOUNT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns checkout coop fund label
     *
     * @param null|int|string $store Mixed Params
     *
     * @return string|null
     */
    public function getCastrolFundLabel($store = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::CASTROL_FUND_LABEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     *  Get Header title
     *
     * @param null|int|string $store Mixed Params
     *
     * @return string|null
     */
    public function getCastrolHeaderTitle($store = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::CASTROL_FUND_HEADER_TITLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns checkout coop fund label
     *
     * @param null|int|string $store Mixed params
     *
     * @return string|null
     */
    public function getCastrolFundOrderMessage($store = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::CASTROL_FUND_ORDER_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is Castrol fund allowed by customer group
     *
     * @param int      $customerGroupId customer group id
     * @param int|null $storeId         store id
     *
     * @return bool
     */
    public function isCastrolFundAllowedByGroupId(
        int $customerGroupId,
        ?int $storeId = null
    ): bool {
        $allowedGroups = $this->getAllowedGroups(
            $storeId
        );

        return in_array($customerGroupId, $allowedGroups);
    }

    /**
     * Return if logged in customer is allowed to use Business Growth Funds
     *
     * @return bool
     */
    public function isCustomerAllowedToUseFunds(): bool
    {
        $allowed = false;

        if ($this->isModuleEnabled()) {
           
            $loggedIn = $this->httpContext->getValue(Context::CONTEXT_AUTH);
            if ($loggedIn) {
                $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
                $allowed = $this->isCastrolFundAllowedByGroupId(
                    (int) $customerGroupId
                );
            }
        }

        return $allowed;
    }
}
