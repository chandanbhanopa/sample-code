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

namespace Helm\ProductKit\Observer\ProductKit;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product\Type;
use Helm\ProductKit\Helper\Data;

/**
 * AddChildProductToCart
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

class AddChildProductToCart implements ObserverInterface
{
    protected $productRepository;
    protected $cartRepository;
    protected $request;
    protected $data;

    protected static $pluginCounter = 1;

    /**
     * Class Constructor
     *
     * @param ProductRepositoryInterface $productRepository productrepository
     * @param CartRepositoryInterface    $cartRepository    cartrepository
     * @param RequestInterface           $request           request
     * @param Data                       $data              Helper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $cartRepository,
        RequestInterface $request,
        Data $data,
    ) {
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
        $this->request = $request;
        $this->data = $data;
    }

    /**
     * Execute function
     *
     * @param Observer $observer observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        
        if (!$this->data->isEnable()) {
            return ;
        }
        $quoteItem = $observer->getEvent()->getQuoteItem();
        $product = $quoteItem->getProduct();
        $post = $this->request->getParams();
        $quote = $quoteItem->getQuote();
        $childStockNotAvailable = false;
        $requestedProductId =  $this->request->getParam('product');
        $totalItemsQty = 0;
        // For configurable products, get the parent item if available
        if ($quoteItem->getParentItem()) {
            $quoteItem = $quoteItem->getParentItem();
        }

        $product = $quoteItem->getProduct();
        $productId = $product->getId();
        $productType = $product->getTypeId();

        $aProduct = $this->productRepository->getById($product->getId());
        $childProductJson = $aProduct->getChildProducts();



        if (self::$pluginCounter == 1) {
            $superAttribute = "super_attribute_".$requestedProductId;
            $requestedQuantity = 0;
            if ($this->request->getParam($superAttribute)) {
                $variantCount = $this->request->getParam($superAttribute);
                if (is_array($variantCount) || $variantCount instanceof \Countable) {
                    $totalCount = count($variantCount);
                } else {
                    $totalCount = 0;
                }

                for ($i = 1; $i <= $totalCount; $i++) {
                    $reqQty = "qty_".$requestedProductId."_".$i;
                    $requestedQuantity += $this->request->getParam($reqQty) ? $this->request->getParam($reqQty) : 0;
                }

            } else {
                $requestedQuantity += $this->request->getParam('qty');
            }
            $totalItemsQty += $requestedQuantity;
           
            if ($childProductJson) {
                $childProductDataArray = json_decode($childProductJson ?? '', true);
                foreach ($childProductDataArray as $childProductData) {
                    $childProductId = $childProductData['id'];
                    $childProductQty = $childProductData['quantity'];
                    $childProduct = $this->productRepository->getById($childProductId);
                    $stockItem = $childProduct->getExtensionAttributes()->getStockItem();
                    if ($stockItem->getIsInStock()) {
                        $totalQty = $requestedQuantity * $childProductQty;
                        $totalItemsQty += $totalQty;
                        $quote->addProduct($childProduct, $totalQty);
                    }
                }
            }
        }
        $existingCartQty = $quote->getItemsQty();
        $quote->setItemsQty($totalItemsQty+$existingCartQty);
        $quote->collectTotals()->save();
        $this->cartRepository->save($quote);
        self::$pluginCounter++;
    }
}
