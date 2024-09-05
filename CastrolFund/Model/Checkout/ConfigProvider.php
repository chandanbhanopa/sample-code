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

namespace Helm\CastrolFund\Model\Checkout;

use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Helm\CastrolFund\Service\CastrolFundService;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 *
 * Coop Fund checkout config provider
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CastrolFundService
     */
    private $castrolFundService;

    /**
     * @var AdminConfiguration
     */
    private $adminConfig;

    /**
     * Class constructor
     *
     * @param CastrolFundService    $castrolFundService
     * @param AdminConfiguration $adminConfig
     */
    public function __construct(
        CastrolFundService    $castrolFundService,
        AdminConfiguration $adminConfig
    ) {
        $this->castrolFundService = $castrolFundService;
        $this->adminConfig     = $adminConfig;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $data = [
            'castrolFundEnabled' => $this->adminConfig->isModuleEnabled(),
            'castrolFundAmount'  => 0,
            'castrolFundLabel' => '',
        ];

        if ($this->adminConfig->isModuleEnabled()) {
                $data['castrolFundAmount']  = $this->castrolFundService->getCustomerInitialBalance();
                $data['castrolFundLabel'] = $this->adminConfig->getCastrolFundLabel();
        }

        return $data;
    }
}
