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
 * Class InitialBalanceAttribute
 */
class InitialBalanceAttribute implements DataPatchInterface
{
    private const INITIAL_BALANCE = 'initial_balance';

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
                self::INITIAL_BALANCE,
                [
                    'type' => 'varchar',
                    'label' => 'Initial Balance',
                    'input' => 'text',
                    'sort_order' => 99,
                    'position' => 21,
                    'required' => false,
                    'visible' => true,
                    'system' => false,
                ]
            );

            $attribute = $eavSetup->getEavConfig()->clear()->getAttribute(
                Customer::ENTITY,
                self::INITIAL_BALANCE
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
