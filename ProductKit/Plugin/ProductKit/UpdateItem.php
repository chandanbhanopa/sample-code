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

namespace Helm\ProductKit\Plugin\ProductKit;

use Magento\Checkout\Model\Cart;
use Helm\ProductKit\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * RemoveChildProductFromCart
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class UpdateItem
{
    protected $data;

    protected $product;

    protected $productRepository;

    protected $cart;

    protected $request;

    protected $cartRepository;

    /**
     * Constructor function
     *
     * @param Data                    $data              data
     * @param Product                 $product           product
     * @param ProductRepository       $productRepository product repository
     * @param Cart                    $cart              cart
     * @param RequestInterface        $request           request
     * @param CartRepositoryInterface $cartRepository    cart repository interface
     */
    public function __construct(
        Data $data,
        Product $product,
        ProductRepository $productRepository,
        Cart $cart,
        RequestInterface $request,
        CartRepositoryInterface $cartRepository
    ) {
        $this->data = $data;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->request = $request;
        $this->cartRepository = $cartRepository;
    }
    /**
     * AfterUpdateItem Method
     *
     * @param Cart   $subject subject
     * @param object $result  result
     * @param array  $data    data
     *
     * @return void
     */
    public function afterUpdateItem(Cart $subject, $result, $data)
    {

        $quote = $subject->getQuote();
        $totalItemsQty = 0;
        $requestedProductId =  $this->request->getParam('product');
        $productId = $this->request->getParam('product');
        $product = $this->productRepository->getById($productId);
        $childProductJson = $product->getChildProducts();
        $requestedProductQty = $this->request->getParam('qty');
        $isProductKit = $product->getProductKit();
        if (($childProductJson) && ($isProductKit)) {
            $childProductDataArray = json_decode($childProductJson ?? '', true);
            foreach ($childProductDataArray as $childProductData) {
                $childProductId = $childProductData['id'];
                $childProductQty = $childProductData['quantity'];
                foreach ($quote->getItems() as $childItem) {
                    if ($childItem->getProductId() == $childProductId) {
                        $childItem->setQty($childProductQty*$requestedProductQty);
                    }
                }
            }
        }
        $quote->collectTotals()->save();
        $this->cartRepository->save($quote);
        return $result;
    }
}
