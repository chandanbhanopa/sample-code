<?php
/**
 * Helm_CastrolFund
 *
 * @copyright Copyright (c) 2024 Helm, LLC
 */

declare(strict_types=1);

namespace Helm\CastrolFund\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ParentTerritoryNumberAttribute
 */
class ParentTerritoryNumberAttribute implements DataPatchInterface
{
    private const PARENT_TERRITORY_NUMBER = 'parent_territory_number';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheirtDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $eavSetup = $this->customerSetupFactory->create(
            [
                'setup' => $this->moduleDataSetup
            ]
        );

        try {
            $eavSetup->addAttribute(
                Customer::ENTITY,
                self::PARENT_TERRITORY_NUMBER,
                [
                    'type' => 'varchar',
                    'label' => 'Parent Territory Number',
                    'input' => 'text',
                    'sort_order' => 99,
                    'position' => 22,
                    'required' => false,
                    'visible' => true,
                    'system' => false,
                ]
            );

            $attribute = $eavSetup->getEavConfig()->getAttribute(
                Customer::ENTITY,
                self::PARENT_TERRITORY_NUMBER
            );

            $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                $this->moduleDataSetup->getTable('customer_form_attribute'),
                [
                    'form_code'    => 'adminhtml_customer',
                    'attribute_id' => $attribute->getAttributeId()
                ]
            );

        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheirtDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheirtDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheirtDoc
     */
    public static function getVersion()
    {
        return '1.1.0';
    }
}
