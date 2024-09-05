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

namespace Helm\ProductKit\Model\ProductLink\CollectionProvider;

/**
 * Customlinked Class
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class Customlinked
{
    /**
     * Undocumented function
     *
     * @param object $product product object
     *
     * @return object product collection
     */
    public function getLinkedProducts($product)
    {
        return $product->getCustomlinkedProducts();
    }
}
