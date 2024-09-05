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

namespace Helm\CastrolFund\Service;

use Exception;
use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Helm\CastrolFund\Logger\CastrolFundLogger;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * CastrolFundService
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class CastrolFundService
{
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var LoggerInterface
     */
    private CastrolFundLogger $logger;

    /**
     * Amin Config
     *
     * @var AdminConfiguration
     */
    private AdminConfiguration $adminConfig;

    /**
     * Amin Config
     *
     * @var AdminConfiguration
     */
    private CustomerSession $customerSession;

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
     * Customer Repository
     *
     * @var CollectionFactory
     */
    protected CollectionFactory $orderCollectionFactory;

    /**
     * Class constructor
     *
     * @param CartRepositoryInterface     $cartRepository
     * @param Session                     $session
     * @param LoggerInterface             $logger
     * @param AdminConfiguration          $adminConfig
     * @param CustomerSession             $customerSession
     * @param UserContextInterface        $userContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param CollectionFactory           $orderCollectionFactory
     *
     */
    public function __construct(
        CartRepositoryInterface     $cartRepository,
        Session                     $session,
        CastrolFundLogger           $logger,
        AdminConfiguration          $adminConfig,
        CustomerSession             $customerSession,
        UserContextInterface        $userContext,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory           $orderCollectionFactory
    ) {
        $this->cartRepository         = $cartRepository;
        $this->session                = $session;
        $this->logger                 = $logger;
        $this->adminConfig            = $adminConfig;
        $this->customerSession        = $customerSession;
        $this->userContext            = $userContext;
        $this->customerRepository     = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Update Initial Balance
     *
     * @return void
     */
    public function updateInitailBalance()
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $quote = $this->getQuote();
            $castrolInitialFunds = $this->getCustomerInitialBalance();
            $quote->setData('available_castrol_fund_amount', $castrolInitialFunds);
            $quote->setData('is_applied_castrol_fund', (int)0);
            try {
                $this->cartRepository->save($quote);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * Applies coop fund for quote
     *
     * @return void
     */
    public function applyCastrolFund()
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            /** @var Quote $quote */
            $quote = $this->getQuote();
            $quote->setData('is_applied_castrol_fund', (int)1);
            
            $availableCastrolFund = (float)$this->getCustomerInitialBalance();
            $grandTotal = $quote->getGrandTotal();

            $availableFundAmount = (float)$quote->getData("available_castrol_fund_amount");
            $remainingAmount = 0;

            if ((float) $availableCastrolFund) {

                //Case : 1
                if ($availableCastrolFund > $grandTotal) {
                    $discountAmount = $grandTotal;
                    $remainingAmount = $availableCastrolFund - $discountAmount;
                }

                //Case : 2
                if ($availableCastrolFund < $grandTotal) {
                    $discountAmount = $availableCastrolFund;
                    $remainingAmount = $availableCastrolFund - $discountAmount;
                }

                //Case : 3
                if ($availableCastrolFund == $grandTotal) {
                    $discountAmount = $grandTotal;
                    $remainingAmount = $availableCastrolFund - $discountAmount;
                }
                //$quote->setData("available_castrol_fund_amount", $remainingAmount);
                $quote->setData("used_castrol_fund_amount", $discountAmount);
                
            }
            $quote->collectTotals();
            try {
                $this->cartRepository->save($quote);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @return CartInterface|null
     */
    public function getQuote(): ?CartInterface
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            return $this->cartRepository->get($this->session->getQuoteId());
        }
    }

    /**
     * Reset all applied coop fund amount in quote and quote item data
     *
     * @return void
     */
    public function cancelCastrolFund()
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $quote = $this->getQuote();
            $quote->setData('is_applied_castrol_fund', (int)0);
            $quote->setData('available_castrol_fund_amount', $this->getCustomerInitialBalance());
            $quote->setData("used_castrol_fund_amount", 0);
            $grandTotal = $this->getCustomerInitialBalance() - $quote->getGrandTotal();
            $quote->setGrandTotal($grandTotal);
            $quote->collectTotals();

            try {
                $this->cartRepository->save($quote);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * Get Customer Initial Funds
     *
     * @return float
     */
    public function getCustomerInitialBalance()
    {
        $initialBalance = 0;
        $customerId = $this->userContext->getUserId();
        if (!$customerId) {
            return $initialBalance;
        }
        $customer = $this->customerRepository->getById($customerId);
        $customerAttributeData = $customer->__toArray();
        if (isset($customerAttributeData['custom_attributes']['initial_balance']['value'])) {
            $initialBalance = $customerAttributeData['custom_attributes']['initial_balance']['value'];
        }
        
        return $initialBalance;
    }

    /**
     * Get Customer Territory Data
     *
     * @return float
     */
    public function getCustomerTerritoryData($customerId = null)
    {
        $territoryNumber = '';
        $parentTerritoryNumber = '';
        $glNumber = '';
        if (!$customerId) {
            $customerId = $this->userContext->getUserId();
        }
        if (!$customerId) {
            $result = [
                'territory_number'=>$territoryNumber,
                'parent_territory_number'=>$parentTerritoryNumber,
                'gl_number'=>$glNumber
            ];
            return $result;
        }
        $customer = $this->customerRepository->getById($customerId);
        $customerAttributeData = $customer->__toArray();
        if (isset($customerAttributeData['custom_attributes']['territory_number']['value'])) {
            $territoryNumber = $customerAttributeData['custom_attributes']['territory_number']['value'];
        }
        if (isset($customerAttributeData['custom_attributes']['parent_territory_number']['value'])) {
            $parentTerritoryNumber = $customerAttributeData['custom_attributes']['parent_territory_number']['value'];
        }
        if (isset($customerAttributeData['custom_attributes']['gl_number']['value'])) {
            $glNumber = $customerAttributeData['custom_attributes']['gl_number']['value'];
        }
        $result = [
            'territory_number'=>$territoryNumber,
            'parent_territory_number'=>$parentTerritoryNumber,
            'gl_number'=>$glNumber
        ];
        return $result;
    }

    /**
     * UpdateCustomerInitialBalance function
     *
     * @param int $amount amount to update
     *
     * @return void
     */
    public function updateCustomerInitialBalance($amount)
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customer->setCustomAttribute('initial_balance', (float)$amount);
            try {
                $this->customerRepository->save($customer);
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * Get coop fund order data
     *
     * @param $order
     *
     * @return array
     */
    public function getCastrolFundOrderData($order): array
    {
        $customerId = $this->userContext->getUserId();
        if (!$customerId) {
            return [
                'used_castrol_fund_amount' => (bool) 0,
                'label'          => '',
                'amount'         => ''
            ];
        }

        return [
            'used_castrol_fund_amount' => (bool) $order->getData('used_castrol_fund_amount'),
            'label'          => $this->adminConfig->getCastrolFundLabel(),
            'amount'         => $order->getData('used_castrol_fund_amount')
        ];
    }

    /**
     * Create Parent Territory Number
     *
     * @param $territoryNumber
     *
     * @return array
     */
    public function createParentTerritoryNumber($territoryNumber): string
    {
        $customerId = $this->userContext->getUserId();
        if (!$customerId) {
            return '';
        }
        // TODO: Change static value with SSO api response.
        $territoryNumber = "BN12";
        $suffix = "0";
        if ($territoryNumber) {
            $parentTerritoryNumber = preg_replace('/[0-9]/', $suffix, $territoryNumber);
        }
        return $parentTerritoryNumber;
    }

    /**
     * Get Customer Balance by Order
     *
     * @param $territoryNumber
     *
     * @return array
     */
    public function getCustomerOrderAmount()
    {
        $customerId = $this->userContext->getUserId();
        if (!$customerId) {
            return '';
        }

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
            return $totalCastrolFundAmount;
        }
    }
}
