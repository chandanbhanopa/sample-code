<?php

namespace Helm\BrandFilterHide\Plugin;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http;
use Helm\BrandFilterHide\Helper\Data;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;

class CheckAuthorisedCustomerGroup
{   
    /**
     * @var Magento\Framework\Controller\ResultFactory $resultFactory
     */
    protected $resultFactory;

    /**
     * @var Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var Magento\Framework\App\Request\Http $requestHttp
     */
    protected $requestHttp;

    /**
     * @var Magento\Framework\UrlInterface $urlInterface
     */
    protected $urlInterface;

    /**
     * @var Helm\BrandFilterHide\Helper\Data $data
     */
    protected $data;

    /**
     * @var Magento\Framework\App\Response\RedirectInterface $redirect
     */
    protected $redirect;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * @param ResultFactory $resultFactory
     * @param StoreManagerInterface $storeManager
     * @param Http $requestHttp
     * @param UrlInterface $urlInterface
     * @param Data $data
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     * @param CustomerFactory $customerFactory
     */

    public function __construct(
        ResultFactory            $resultFactory,
        StoreManagerInterface    $storeManager,
        Http                     $requestHttp,
        UrlInterface             $urlInterface,
        Data                     $data,
        RedirectInterface        $redirect,
        ManagerInterface         $messageManager,
        CustomerFactory          $customerFactory
        
    ) {
        $this->resultFactory   = $resultFactory;
        $this->storeManager    = $storeManager;
        $this->requestHttp     = $requestHttp;
        $this->urlInterface    = $urlInterface;
        $this->data            = $data;
        $this->redirect        = $redirect;
        $this->messageManager  = $messageManager;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param FrontControllerInterface $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return void
     */
    public function afterDispatch(
        FrontControllerInterface $subject,
        $result
    ) {
        if (!$this->data->isEnable()) {
            return $result;
        }
        /**
         * Check customer is logged in or not.
         */
        if(!$this->data->getUserContext()->getUserId()) {
            return $result;
        }

        if($this->requestHttp->getActionName() == "restriction") {
            return $result;
        } 
        $customerId = $this->data->getUserContext()->getUserId();
        $customerModel = $this->customerFactory->create();
        $customer = $customerModel->load($customerId);
        $currentCustomerGroupId = $customer->getGroupId();
        $storeId = $customer->getStoreId();
        $currentStore = $this->storeManager->getStore()->getCode();
        $registeredUserStore = $this->storeManager->getStore($storeId);
        $customerStoreRedirectUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
        // #Get Restriced Groups from the admin
        $restrictedGroups = $this->data->getRestrictedGroups();
        $restrictedGroupsArray = explode(",", $restrictedGroups ?? "");
        if (!in_array($currentCustomerGroupId, $restrictedGroupsArray) && $restrictedGroups != "") {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $permissionUrl = $customerStoreRedirectUrl."brand/permission/restriction";
            $resultRedirect->setUrl($permissionUrl);
            return $resultRedirect;
        } else {
            return $result;
        }
        return $result;
    }
}
