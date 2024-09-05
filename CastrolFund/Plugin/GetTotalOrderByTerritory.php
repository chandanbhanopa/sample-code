<?php
/**
 * Helm
 *
 * Get Total Order By Territory
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

namespace Helm\CastrolFund\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\API\CustomerRepositoryInterface;
use Helm\CastrolFund\Logger\CastrolFundLogger;
use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Magento\Authorization\Model\UserContextInterface;
use Helm\CastrolFund\Service\CastrolFundService;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Customer login plugin
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

class GetTotalOrderByTerritory
{

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var Magento\Customer\API\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Helm\CastrolFund\Model\Config\AdminConfiguration
     */
    protected $adminConfiguration;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var CastrolFundService
     */
    protected $castrolFundService;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerRepositoryInterface $customerRepository
     *
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        OrderInterface $orderInterface,
        AdminConfiguration $adminConfiguration,
        UserContextInterface $userContext,
        CastrolFundService $castrolFundService,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderInterface = $orderInterface;
        $this->adminConfiguration = $adminConfiguration;
        $this->userContext = $userContext;
        $this->castrolFundService = $castrolFundService;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }
    /**
     * Change redirect after login to home instead of dashboard.
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Magento\Framework\Controller\Result\Redirect $result
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $result
    ) {

        if (!$this->adminConfiguration->isModuleEnabled()) {
            return $result;
        }
        $isAllowed = $this->adminConfiguration->isCustomerAllowedToUseFunds();
        if (!$isAllowed) {
            return $result;
        }

        $customerId = $this->userContext->getUserId();
        $customer = $this->customerRepository->getById($customerId);
        $customerAttributeData = $customer->__toArray();
        $year = date("Y");
        if (isset($customerAttributeData['custom_attributes']['territory_number']['value'])) {
            $territoryNumber = $customerAttributeData['custom_attributes']['territory_number']['value'];
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('customer_id', $customerId);
            $orderCollection->addFieldToFilter('territory_number', $territoryNumber);
            $orderCollection->addFieldToFilter('is_applied_castrol_fund', 1);
            $orderCollection->addFieldToFilter('status', ['neq' => 'canceled']);
            $conditions['total_castrol_fund'] = 'SUM(main_table.used_castrol_fund_amount)';

            $orderCollection->getSelect()
            ->columns($conditions)
            ->where('DATE_FORMAT(created_at, "%Y") = ?', $year);

            $orderCollection->load();

            $totalCastrolFundAmount = (float)$orderCollection->getFirstItem()->getData('total_castrol_fund');
            $customerBalance = $customer->getCustomAttribute('initial_balance')->getValue();

        }

        return $result;
    }
}
