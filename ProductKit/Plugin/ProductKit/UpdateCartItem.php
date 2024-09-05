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
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\CartRepositoryInterface;

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
    protected $cartRepository;

    /**
     * Constructor function
     *
     * @param Data                    $data              helper class
     * @param Product                 $product           product
     * @param ProductRepository       $productRepository product repository
     * @param Cart                    $cart              cart
     * @param RequestInterface        $request           request
     * @param CartItemInterface       $cartItemIterface  cartItem interface
     * @param CartRepositoryInterface $cartRepository    cart repository
     */
    public function __construct(
        Data $data,
        Product $product,
        ProductRepository $productRepository,
        Cart $cart,
        RequestInterface $request,
        CartItemInterface $cartItemIterface,
        CartRepositoryInterface $cartRepository
    ) {
        $this->data = $data;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->request = $request;
        $this->cartItemInterface = $cartItemIterface;
        $this->cartRepository = $cartRepository;
    }
    
    /**
     * AfterUpdateItems Method
     *
     * @param Cart   $subject subject
     * @param object $result  result
     *
     * @return void
     */
   
    /**
     * AfterUpdateItems Method
     *
     * @param Cart   $subject subject
     * @param object $result  result
     *
     * @return void
     */
    public function afterUpdateItems(Cart $subject, $result)
    {
        if (!$this->data->isEnable()) {
            return $result;
        }
        $post = $this->request->getParam('cart');
        $quote = $subject->getQuote();
        $totalItemsQty = 0;
        $this->_processPostData($post, $quote);

        $totalItemsQty += $quote->getItemsQty();
        $quote->setItemsQty($totalItemsQty);
        $quote->collectTotals()->save();
        $this->cartRepository->save($quote);
        return $result;
    }

    /**
     * Process Post Data
     *
     * @param array  $post  post data
     * @param object $quote quote object
     * @return void
     */
    private function _processPostData($post, $quote)
    {
        if (isset($post)) {
            foreach ($post as $quoteItemId => $requestCart) {
                $this->_processQuoteItem($quote, $quoteItemId, $requestCart);
            }
        }
    }

    /**
     * Process Quote Items
     *
     * @param object $quote quote object
     * @param int $quoteItemId quote item id
     * @param array $requestCart requested cart
     * @return void
     */
    private function _processQuoteItem($quote, $quoteItemId, $requestCart)
    {
        foreach ($quote->getItems() as $childItem) {
            if ($quoteItemId == $childItem->getId()) {
                $productId = $childItem->getProductId();
                $product = $this->_getProductById($productId);
                if (!$product) {
                    continue;
                }
                $this->_processProductKit($product, $requestCart, $quote);
            }
        }
    }

    /**
     * Check the product is available or not
     *
     * @param int $productId
     * @return void
     */
    private function _getProductById($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null; // Return null if the product doesn't exist
        }
    }

    /**
     * Process Product kit
     *
     * @param object $product     product object
     * @param object $requestCart request cart
     * @param object $quote       quote object
     * @return void
     */
    private function _processProductKit($product, $requestCart, $quote)
    {
        $isProductKit = $product->getProductKit();
        if ($product->getChildProducts() && $isProductKit) {
            $parentProductQty = $requestCart['qty'];
            $childProductJson = $product->getChildProducts();
            
            $totalRequtestQty = 0;
            if (isset($requestCart['main_product'])) {
                $totalRequtestQty += $requestCart['qty'];
            }
            $totalItemsQty = $parentProductQty;
            $childProductDataArray = json_decode($childProductJson ?? '', true);
            if (is_array($childProductDataArray)) {
                foreach ($childProductDataArray as $childProductData) {
                    $params=[];
                    $params["childProductData"] = $childProductData;
                    $params["quote"] = $quote;
                    $params["totalRequtestQty"] = $totalRequtestQty;
                    $params["product"] = $product;
                    $params["parentProductQty"] = $parentProductQty;
                    $this->_processChildProduct($params, $totalItemsQty);
                }
            }
        }
    }

    /**
     * This function will process the child product
     *
     * @param array  $childProductData child product data
     * @param object $quote quote      object
     * @param int    $totalRequtestQty total requested quantity
     * @param object $product product  object
     * @param int    $parentProductQty parent product qty
     * @param int    $totalItemsQty    total item qty
     * @return void
     */
    private function _processChildProduct($params, &$totalItemsQty)
    {
        
        $childProductData = $params["childProductData"];
        $quote= $params["quote"];
        $totalRequtestQty= $params["totalRequtestQty"];
        $product= $params["product"];
        $parentProductQty= $params["parentProductQty"];
    
        $childProductId = $childProductData['id'];
        $childProductQty = $childProductData['quantity'];

        // Find the corresponding child item in the quote
        foreach ($quote->getItems() as $quoteItem) {
            if ($quoteItem->getProductId() == $childProductId) {
                $quoteItem->setQty($totalRequtestQty * $childProductQty);
                $quoteItem->setParentId($product->getId());
                $totalItemsQty += $parentProductQty * $childProductQty;
                $quoteItem->save();
            }

            if ($quoteItem->getProductId() == $product->getId()) {
                $quoteItem->setQty($parentProductQty);
                $quoteItem->setParentId($product->getId());
                $quoteItem->save();
            }
        }
    }
}
