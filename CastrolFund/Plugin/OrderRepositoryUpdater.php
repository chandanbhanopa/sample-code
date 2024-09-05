<?php
/**
 * Helm
 *
 * Plugin for order repository update
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

namespace Helm\CastrolFund\Plugin;

use Helm\CastrolFund\Model\Config\AdminConfiguration;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Helm\CastrolFund\Service\CastrolFundService;
use Helm\CastrolFund\Logger\CastrolFundLogger;

/**
 * OrderRepositoryUpdater
 *
 * @category  CastrolFund
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class OrderRepositoryUpdater
{
    
    /**
     * Order Extension Factory
     *
     * @var OrderExtensionFactory
     */
    private $extensionFactory;

    /**
     * CastrolFundLogger object
     *
     * CastrolFundLogger $castrolFundLogger CartRepository
     */
    protected CastrolFundLogger $castrolFundLogger;

    /**
     * Object copy service
     *
     * @var CastrolFundService $castrolFundService object
     */
    protected CastrolFundService $castrolFundService;

    /**
     * Class constructor
     *
     * @param OrderExtensionFactory $extensionFactory   Extension factory
     * @param CastrolFundLogger     $castrolFundLogger  Fundlogger
     * @param CastrolFundService    $castrolFundService Fundservice
     */
    public function __construct(
        OrderExtensionFactory $extensionFactory,
        CastrolFundLogger $castrolFundLogger,
        CastrolFundService $castrolFundService
    ) {
        $this->extensionFactory   = $extensionFactory;
        $this->castrolFundLogger  = $castrolFundLogger;
        $this->castrolFundService = $castrolFundService;
    }

    /**
     * Order extension attributes
     *
     * @param OrderRepositoryInterface $subject order repository
     * @param OrderInterface           $result  order interface
     *
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface           $result
    ): OrderInterface {
       
        $customerId = $result->getCustomerId();
        $extensionAttributes = $result->getExtensionAttributes();
        $territoryData = $this->castrolFundService->getCustomerTerritoryData($customerId);
        $extensionAttributes->setTerritoryNumber($territoryData['territory_number']);
        $extensionAttributes->setParentTerritoryNumber($territoryData['parent_territory_number']);
        $extensionAttributes->setGlNumber($territoryData['gl_number']);
        $result->setExtensionAttributes($extensionAttributes);
        return $result;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        $result
    ) {
        // We do the same thing here, and can save some time by passing the logic to afterGet.
        foreach ($result->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $result;
    }
}
