<?php
/**
 * Helm_CastrolFund
 *
 * @copyright Copyright (c) 2024 Helm, LLC
 */

declare(strict_types=1);

namespace Helm\CastrolFund\ViewModel;

use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Magento\Customer\Model\Context;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CastrolFund
 */
class CastrolFund implements ArgumentInterface
{
    /**
     * Customer HttpContext
     *
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * AdminConfiguration
     *
     * @var AdminConfiguration
     */
    protected $adminConfiguration;

    /**
     * Customer Session
     *
     * @var Session
     */
    protected $session;

    /**
     * Customer Repository
     *
     * @var object Customer
     */
    protected $customerRepository;

    public function __construct(
        AdminConfiguration $adminConfiguration,
        HttpContext $httpContext,
        Session $session,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->adminConfiguration = $adminConfiguration;
        $this->httpContext = $httpContext;
        $this->session     = $session;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Return if customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * Return if logged in customer is allowed to use Coop Funds
     *
     * @return bool
     */
    public function isCustomerAllowedToUseFunds(): bool
    {
        $allowed = false;

        if ($this->adminConfiguration->isModuleEnabled()) {
            $loggedIn = $this->httpContext->getValue(Context::CONTEXT_AUTH);

            if ($loggedIn) {
                $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
                $allowed = $this->adminConfiguration->isCastrolFundAllowedByGroupId(
                    (int) $customerGroupId
                );
            }
        }

        return $allowed;
    }

    /**
     * Return Coop Fund Label
     *
     * @return string
     */
    public function getCastrolFundLabel(): string
    {
        return $this->adminConfiguration->getCastrolFundLabel();
    }

    /**
     * Return Header Title
     *
     * @return string
     */
    public function getCastrolHeaderTitle(): string
    {
        $castrolFundTitle = $this->adminConfiguration->getCastrolHeaderTitle();
        return $castrolFundTitle ? $castrolFundTitle: "Your Initial Budget" ;
    }

    /**
     * Get Initial Balance
     *
     * @return string $initailBalance
     */
    public function getInitialBalance()
    {
        $loggedIn = $this->httpContext->getValue(Context::CONTEXT_AUTH);
        $customer = $this->session->getCustomer();
        return $customer->getData('initial_balance');
    }
}
