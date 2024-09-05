<?php
/**
 * Helm
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Helm
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

namespace Helm\ProductKit\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product as MainProduct;

/**
 * Product Class
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class Product extends MainProduct
{

    const LINK_TYPE_CUSTOMLINKED = 17;

    /**
     * Retrieve array of related products
     *
     * @return array
     */
    public function getCustomlinkedProducts()
    {
        //Get Object Manager Instance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $helper = $objectManager->create(\Helm\ProductKit\Helper\Data::class);
        $store = $helper->getStoreData();
        if (!$this->hasCustomlinkedProducts()) {
            $products = [];
            $collection = $this->getCustomlinkedProductCollection();
            $collection->addAttributeToFilter('type_id', Type::TYPE_SIMPLE);
            $collection->addAttributeToFilter('visibility', ['eq' => Visibility::VISIBILITY_BOTH]);
            $collection->addStoreFilter($store);

            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setCustomlinkedProducts($products);
        }
        return $this->getData('customlinked_products');
    }

    /**
     * Retrieve related products identifiers
     *
     * @return array
     */
    public function getCustomlinkedProductIds()
    {
        if (!$this->hasCustomlinkedProductIds()) {
            $ids = [];
            foreach ($this->getCustomlinkedProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setCustomlinkedProductIds($ids);
        }
        return [$this->getData('customlinked_product_ids')];
    }
    
    /**
     * Retrieve collection related product
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function getCustomlinkedProductCollection()
    {
        $collection = $this->_getKitProductCollection()->getProductCollection()->setIsStrongMode();
        $collection->setProduct($this);

        return $collection;
    }

    /**
     * Get Product collection
     *
     * @return object
     */
    private function _getKitProductCollection()
    {
        return $this->getLinkInstance()->setLinkTypeId(static::LINK_TYPE_CUSTOMLINKED);
    }
}
