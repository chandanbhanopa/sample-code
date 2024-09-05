<?php
/**
 * Helm
 *
 * CastrolFund Interface
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

namespace Helm\CastrolFund\Api;

/**
 * CastrolFundInterface
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 *
 * @api
 */

interface CastrolFundInterface
{
    /**
     * Apply castrol funds
     *
     * @return void
     */
    public function apply(): void;

    /**
     * Reset castrol fund
     *
     * @return void
     */
    public function reset(): void;

    /**
     * Cancel castrol fund
     *
     * @return void
     */
    public function cancel(): void;

    /**
     * Update Initial Balance In Quote
     *
     * @return void
     */
    public function updateFundsInQuote(): void;

    /**
     * Get Initial Funds
     *
     * @return string
     */
    public function getInitailFunds(): string;
}
