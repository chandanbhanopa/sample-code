<?php
/**
 * Helm
 *
 * @package    BrandFilterHide
 * @version    1.0.0
 */

namespace Helm\BrandFilterHide\Plugin;

use Helm\BrandFilterHide\Helper\Data;
use Magento\Customer\API\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Login post plugin
 */
class LoginPostPlugin
{
    /**
     * @var Helm\BrandFilterHide\Helper\Data
     */
    protected $data;

    /**
     * @var Magento\Customer\API\CustomerRepositoryInterface
     */
    protected $customerRepository;
  
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storeConfig;

    /**
     * @var Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Data $data
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeConfig
     * @param ResultFactory $resultFactory
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Data $data,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeConfig,
        ResultFactory $resultFactory,
        RedirectInterface $redirect,
        ManagerInterface $messageManager
    ) {
        $this->data = $data;
        $this->customerRepository = $customerRepository;
        $this->storeConfig = $storeConfig;
        $this->resultFactory = $resultFactory;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
    }
    /**
     * Change redirect after login to home instead of dashboard.
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Magento\Framework\Controller\Result\Redirect $result
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        callable $proceed
    ) {
        if (!$this->data->isEnable()) {
            return $this;
        }
        #Get Restriced Groups from the admin
        $restrictedGroups = $this->data->getRestrictedGroups();
        $restrictedGroupsArray = explode(",", $restrictedGroups ?? "");
        $postData = $subject->getRequest()->getParam('login', false);
        $emailId = $postData['username'];
        $currentCustomerGroupId = $this->customerRepository->get($emailId)->getGroupId();
        if (!in_array($currentCustomerGroupId, $restrictedGroupsArray) && $restrictedGroups != "") {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->redirect->getRedirectUrl());
            $message = __('Please check you have sufficient access.');
            $this->messageManager->addErrorMessage($message);
            return $resultRedirect;
        } else {
            return $proceed();
        }
    }
}
