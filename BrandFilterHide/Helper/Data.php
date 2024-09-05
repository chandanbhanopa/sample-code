<?php
/**
 * Helm
 * Helper for admin setting configuration
 * @package    BrandFilterHide
 * @version    1.0.0
 */
declare(strict_types=1);
namespace Helm\BrandFilterHide\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Helper Class Data
 */
class Data extends AbstractHelper
{
    // Configuration paths as constants for better readability and maintainability
    const XML_MODULE_ENABLE = "helm_customer_brandfilter/general/enable";
    const RSTRICTED_GROUP_FILTER = "helm_customer_brandfilter/general/helm_customer_brandfilter_customer_group";
    const HIDE_BRAND_SWITCHER = "helm_customer_brandfilter/general/hide_brand_switcher";
     
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var Magento\Authorization\Model\UserContextInterface $userContext
     */
    protected $userContext;

    /**
     * Class constructor
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param Context $httpContext
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Context $httpContext,
        StoreManagerInterface $storeManager,
        UserContextInterface $userContext
    ) {
        $this->scopeConfig     = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->httpContext     = $httpContext;
        $this->storeManager    = $storeManager;
        $this->userContext     = $userContext;
    }

   /**
    * Check Module is enable or disable
    * @return boolean
    */
    public function isEnable()
    {
        // Get the value of the XML_MODULE_ENABLE configuration path
        $isEnable = $this->scopeConfig->getValue(
            self::XML_MODULE_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
        return (bool) $isEnable; // Cast the value to boolean before returning
    }

    /**
     * Retrieve the usergroup name from configuration
     * @return string
     */
    public function getRestrictedGroups()
    {
        // Get the value of the RSTRICTED_GROUP_FILTER configuration path
        $restrictedGroup = $this->scopeConfig->getValue(
            self::RSTRICTED_GROUP_FILTER,
            ScopeInterface::SCOPE_STORE
        );
        return (string) $restrictedGroup; // Cast the value to string before returning
    }

    /**
     * Retrieve the custrent customer
     * @return string
     */
    public function allowedUsersGroup()
    {
        $restrictedGroups = $this->getRestrictedGroups();
        $restrictedGroupsArray = explode(",", $restrictedGroups);
        return $restrictedGroupsArray;
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
     * Check is menu restricted
     * @return boolean
     */
    public function getStoreData()
    {
        return $this->storeManager;
    }

    /**
     * Check brand switcher hide or show
     * @return boolean
     */
    public function isHideBrandSwitcher()
    {
        $hideForGroups = $this->scopeConfig->getValue(
            self::HIDE_BRAND_SWITCHER,
            ScopeInterface::SCOPE_STORE
        );
        return  $hideForGroups; // Cast the value to boolean before returning
    }

    /**
     * Get Current Customer Group
     * @return integer
     */
    public function getCurrentCustomerGroup()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
    }

    public function getUserContext()
    {
        return $this->userContext;
    }
}


