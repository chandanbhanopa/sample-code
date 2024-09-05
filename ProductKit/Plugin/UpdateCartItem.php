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

namespace Helm\ProductKit\Plugin;

use Magento\Checkout\Model\Cart;
use Helm\ProductKit\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * UpdateCartItem Plugin
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class UpdateCartItem
{

    protected $data;

    protected $product;

    protected $productRepository;

    protected $cart;

    protected $request;

    protected $cartItemInterface;

    /**
     * Constructor function
     *
     * @param Data              $data              helper class
     * @param Product           $product           product
     * @param ProductRepository $productRepository product repository
     * @param Cart              $cart              cart
     * @param RequestInterface  $request           request
     * @param CartItemInterface $cartItemIterface  cartItem interface
     */
    public function __construct(
        Data $data,
        Product $product,
        ProductRepository $productRepository,
        Cart $cart,
        RequestInterface $request,
        CartItemInterface $cartItemIterface
    ) {
        $this->data = $data;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->request = $request;
        $this->cartItemInterface = $cartItemIterface;
    }
    
    /**
     * AfterUpdateItems function
     *
     * @param Cart   $subject subject
     * @param object $result  result
     *
     * @return void
     */
    public function afterUpdateItems(Cart $subject, $result)
    {
        return $result;
    }
}
