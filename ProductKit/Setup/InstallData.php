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
namespace Helm\ProductKit\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Helm\ProductKit\Model\Product\Link;

/**
 * InstallData
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class InstallData implements InstallDataInterface
{
   
    /**
     * Install function
     *
     * @param ModuleDataSetupInterface $setup   moduledata interface
     * @param ModuleContextInterface   $context context interface
     *
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $data = [
            ['link_type_id' => Link::LINK_TYPE_CUSTOMLINKED, 'code' => 'customlinked'],
        ];
        foreach ($data as $bind) {
            $setup->getConnection()
                ->insertForce($setup->getTable('catalog_product_link_type'), $bind);
        }
        $data = [
            [
                'link_type_id' =>Link::LINK_TYPE_CUSTOMLINKED,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int',
            ]
        ];
        $setup->getConnection()
            ->insertMultiple($setup->getTable('catalog_product_link_attribute'), $data);
    }
}
