<?php
/**
 * Helm
 *
 * Class to catrol fund operations
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

namespace Helm\CastrolFund\Model;

use Helm\CastrolFund\Api\CastrolFundInterface;
use Helm\CastrolFund\Service\CastrolFundService;
use Helm\CastrolFund\Logger\CastrolFundLogger;

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

class CastrolFunds implements CastrolFundInterface
{
    /**
     * Castrol Fund Service
     *
     * @var CastrolFundService
     */
    private $castrolFundService;

    /**
     * Castrol Fund logger
     *
     * @var CastrolFundLogger
     */
    private $castrolFundLogger;

    /**
     * Class constructor
     *
     * @param CoopFundManager $coopFundManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        CastrolFundService $castrolFundService,
        CastrolFundLogger $castrolFundLogger
    ) {
        $this->castrolFundService = $castrolFundService;
        $this->castrolFundLogger = $castrolFundLogger;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(): void
    {
        $this->castrolFundLogger->info("Apply Method Called");
        try {
            $this->castrolFundService->applyCastrolFund();
        } catch (\Exception $e) {
            $this->castrolFundLogger->critical($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        try {
            $this->castrolFundService->resetCoopFund();
        } catch (\Exception $e) {
            $this->castrolFundLogger->critical($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function cancel(): void
    {
        try {
            $this->castrolFundService->cancelCastrolFund();
        } catch (\Exception $e) {
            $this->castrolFundLogger->critical($e->getMessage());
        }
    }

    /**
     * Update funds in customer quote
     *
     * @return void
     */
    public function updateFundsInQuote(): void
    {
        try {
            $this->castrolFundService->updateInitailBalance();
        } catch (\Exception $e) {
            $this->castrolFundLogger->critical($e->getMessage());
        }
    }

    /**
     * Get Customer Initail Funds
     *
     * @return string
     */
    public function getInitailFunds(): string
    {
        $territoryData = $this->castrolFundService->getCustomerTerritoryData();
        $orderAmount = $this->castrolFundService->getCustomerOrderAmount();
        $terrirtoryNumber = $territoryData['territory_number'];
        try {
            if ($this->castrolFundService->getCustomerInitialBalance()) {
                $finalAmount = (float)$this->castrolFundService->getCustomerInitialBalance() - $orderAmount;
                $finalAmount = $finalAmount > 0 ? (string)$finalAmount : (string)0;
                return "$". $finalAmount;
            }
        } catch (\Exception $e) {
            $this->castrolFundLogger->critical($e->getMessage());
        }

        return '';
    }
}
