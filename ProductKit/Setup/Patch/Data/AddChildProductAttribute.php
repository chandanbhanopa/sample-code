<?php
/**
 * Helm
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Helm.
 * Create a product attribute
 *
 * PHP version 8.2
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

namespace Helm\ProductKit\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

/**
 * AddChildProductAttribute
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class AddChildProductAttribute implements DataPatchInterface
{
    protected $moduleDataSetup;
    protected $eavSetupFactory;

    /**
     * Constructor function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup setup
     * @param EavSetupFactory          $eavSetupFactory eavsetupfactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply function
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'child_products',
            [
                'label' => 'Child Products',
                'type'  => 'text',
                'input' => 'text',
                'required' => false,
                'sort_order' => 1,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'used_in_product_listing' => true,
                'visible_on_front' => false,
                'visible' => false
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'product_kit',
            [
                'label' => 'Product Kit',
                'type' => 'int',
                'input' => 'boolean',
                'source' => Boolean::class,
                'required' => false,
                'sort_order' => 1,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'used_in_product_listing' => true,
                'visible_on_front' => false,
                'visible' => false
            ]
        );
    }

    /**
     * GetDependencies function
     *
     * @return void
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * GetAliases function
     *
     * @return void
     */
    public function getAliases()
    {
        return [];
    }
}
