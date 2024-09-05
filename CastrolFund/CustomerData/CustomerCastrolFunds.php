<?php
/**
 * Helm
 *
 * Service Class for quote management
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

namespace Helm\CastrolFund\CustomerData;

use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Helm\CastrolFund\Logger\CastrolFundLogger;
use Helm\CastrolFund\Service\CastrolFundService;

/**
 * CustomerCoopFunds
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class CustomerCastrolFunds implements SectionSourceInterface
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var AdminConfiguration $adminConfiguration
     */
    private AdminConfiguration $adminConfiguration;

    /**
     * User Context
     *
     * @var UserContextInterface
     */
    protected UserContextInterface $userContext;

    /**
     * Customer Repository
     *
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * Logger
     *
     * @var CastrolFundLogger
     */
    protected CastrolFundLogger $castrolFundLogger;

    /**
     * Castrol Fund Service
     *
     * @var CastrolFundService
     */
    protected CastrolFundService $castrolFundService;
    
    /**
     * Constructor function
     *
     * @param AdminConfiguration          $adminConfiguration admin configuration
     * @param UserContextInterface        $userContext        user context
     * @param CustomerRepositoryInterface $customerRepository customer repository
     * @param CastrolFundLogger           $castrolFundLogger  Castrol fund logger
     */
    public function __construct(
        AdminConfiguration $adminConfiguration,
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository,
        CastrolFundLogger $castrolFundLogger,
        CastrolFundService $castrolFundService
    ) {
        $this->adminConfiguration = $adminConfiguration;
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->castrolFundLogger = $castrolFundLogger;
        $this->castrolFundService = $castrolFundService;
    }

    /**
     * Provides user coop funds data
     *
     * @return array
     */
    public function getSectionData():array
    {
        
        $result = [];
        $result['castrolFundsEnable'] = $this->adminConfiguration->isModuleEnabled();
        $result['castrolFundsAllowed'] = $this->adminConfiguration->isCustomerAllowedToUseFunds();
       
        $customerId = $this->userContext->getUserId();
        $customer = $this->customerRepository->getById($customerId);
        
        $customerAttributeData = $customer->__toArray();
       
        if (isset($customerAttributeData['custom_attributes']['initial_balance']['value'])) {
            $initialBalance = $customerAttributeData['custom_attributes']['initial_balance']['value'];
            $finalBalance = (float)$initialBalance - (float)$this->castrolFundService->getCustomerOrderAmount();
            $result['initial_balance'] = $finalBalance;
            $result['castrolFundsLabel'] = $this->adminConfiguration->getCastrolFundLabel();
            $result['castrolFundsHeaderTitle'] = $this->adminConfiguration->getCastrolHeaderTitle();
        } else {
            $result['initial_balance'] = 0;
            $result['initial_balance'] = '';
            $result['castrolFundsLabel'] = '';
            $result['castrolFundsHeaderTitle'] = '';
        }

        return $result;
    }
}
