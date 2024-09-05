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

use Exception;
use Magento\Checkout\Model\Cart;
use Helm\ProductKit\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

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
class AddChildProductToCart
{

    protected $data;

    protected $product;

    protected $productRepository;

    protected $cart;

    protected $request;
    protected $executed = false;

    protected $messageManager;

    /**
     * Constructor function
     *
     * @param Data              $data              Helper
     * @param Product           $product           Product
     * @param ProductRepository $productRepository ProductRepository
     * @param Cart              $cart              cart object
     * @param RequestInterface  $request           request data
     * @param ManagerInterface  $messageManager    message manager
     */
    public function __construct(
        Data $data,
        Product $product,
        ProductRepository $productRepository,
        Cart $cart,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        $this->data = $data;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }
    
    /**
     * BeforeAddProduct function
     *
     * @param Cart   $subject     subject
     * @param object $productInfo product info
     * @param object $requestInfo request data
     *
     * @return void
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {

        $requestedProductId =  $this->request->getParam('product');
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
        
        $notInStock = [];
        $exceedStockQty = [];
        if ($productInfo->getId()) {
            $childProducts = json_decode($productInfo->getChildProducts() ?? '', true);
            if (!$childProducts) {
                return [$productInfo,$requestInfo];
            } 
            foreach ($childProducts as $childProduct) {
                $product = $this->productRepository->getById($childProduct['id']);
                $childProductQty = $childProduct['quantity'];
                $stockItem = $product->getExtensionAttributes()->getStockItem();
                $childProductAvailableQtyInStock = $stockItem->getQty();
                $requestedChildQty = $requestedQuantity * $childProductQty;
                if (!$stockItem->getBackOrders()) {
                    
                    if ($requestedChildQty > $childProductAvailableQtyInStock) {
                        $exceedStockQty[] = $product->getName();
                    }
                    
                }
                
                if (!$stockItem->getIsInStock()) {
                    $notInStock[] = $product->getName();
                }
            }
        }
        if (!empty($exceedStockQty)) {
            $productString = implode(", ", $exceedStockQty);
            $msg = __("The parent product's quantity cannot be more than the available stock of its child products. - $productString");
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
        
        if (!empty($notInStock)) {
            $productString = implode(", ", $notInStock);
            throw new \Magento\Framework\Exception\LocalizedException(__($productString. " not available in stock."));
        }
       
        return [$productInfo,$requestInfo];
    }
}
