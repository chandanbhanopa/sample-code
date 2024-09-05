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
use Magento\Framework\Message\ManagerInterface;
use Helm\ProductKit\Model\ProductLink\CollectionProvider\Customlinked;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory;

/**
 * RemoveItem Plugin
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class RemoveItem
{

    protected $data;

    protected $product;

    protected $productRepository;

    protected $cart;

    protected $request;

    protected $cartRepository;

    protected $messageManager;

    protected $customLikedProducts;

    protected $productLinkCollection;

    /**
     * Construtor function
     *
     * @param Data                    $data                  helper
     * @param Product                 $product               product
     * @param ProductRepository       $productRepository     product repository
     * @param Cart                    $cart                  cart
     * @param RequestInterface        $request               request
     * @param CartRepositoryInterface $cartRepository        cart repository
     * @param ManagerInterface        $messageManager        message manager
     * @param Customlinked            $customLikedProducts   custom linked product
     * @param CollectionFactory       $productLinkCollection product collection
     */
    public function __construct(
        Data $data,
        Product $product,
        ProductRepository $productRepository,
        Cart $cart,
        RequestInterface $request,
        CartRepositoryInterface $cartRepository,
        ManagerInterface $messageManager,
        Customlinked $customLikedProducts,
        CollectionFactory $productLinkCollection
    ) {
        $this->data = $data;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->request = $request;
        $this->cartRepository = $cartRepository;
        $this->messageManager = $messageManager;
        $this->customLikedProducts = $customLikedProducts;
        $this->productLinkCollection =  $productLinkCollection;
    }
    
    /**
     * AfterRemoveItem function
     *
     * @param Cart   $subject subject
     * @param object $result  result
     *
     * @return void
     */
    public function afterRemoveItem(Cart $subject, $result)
    {
        //quote item id
        $requestedItemId = $this->request->getParam('id');
        $quote = $subject->getQuote();
        foreach ($quote->getItems() as $item) {
            if ($item->getId() == $requestedItemId) {
                $requestedProductId = $item->getProductId();
                $product = $this->productRepository->getById($requestedProductId);
                $childProductJson = $product->getChildProducts();
                $isProductKit = $product->getProductKit();
                
                if (($childProductJson) && ($isProductKit)) {
                    $childProductDataArray = json_decode($childProductJson ?? '', true);
                    foreach ($childProductDataArray as $childProductData) {
                        $childProductId = $childProductData['id'];
                        foreach ($quote->getAllItems() as $item) {
                            if ($item->getProductId() == $childProductId) {
                                $quote->removeItem($item->getId());
                            }
                        }
                    }
                    $this->cartRepository->save($quote);
                } else {
                    return $result;
                   
                }
            }
        }
        return $result;
    }

    /**
     * BeforeRemoveItem function
     *
     * @param Cart   $subject subject
     * @param object $result  result
     *
     * @return void
     */
    public function beforeRemoveItem(Cart $subject, $result)
    {
        //quote item id
        $requestedItemId = $this->request->getParam('id');
        $productLinkCollection = $this->productLinkCollection->create();
        $productLinkCollection->getSelect()->joinInner(
            ['link' => "catalog_product_link"],
            'e.entity_id = link.linked_product_id'
        )->where("link.link_type_id = 17");
       
        $quote = $subject->getQuote();
        foreach ($quote->getItems() as $item) {
            if ($item->getId() == $requestedItemId) {
                $requestedProductId = $item->getProductId();
                $product = $this->productRepository->getById($requestedProductId);
                $childProductJson = $product->getChildProducts();
                $isProductKit = $product->getProductKit();
                if (($childProductJson) && ($isProductKit)) {
                    $childProductDataArray = json_decode($childProductJson ?? '', true);
                    foreach ($childProductDataArray as $childProductData) {
                        $childProductId = $childProductData['id'];
                        foreach ($quote->getAllItems() as $item) {
                            if ($item->getProductId() == $childProductId) {
                                $quote->removeItem($item->getId());
                            }
                        }
                    }
                    $this->cartRepository->save($quote);
                } elseif ($this->_isChildProduct($productLinkCollection, $item->getProductId())) {
                    return $result;
                   
                } else {
                    return $result;
                }
            }
        }
        return $result;
    }

    /**
     * IsChildProduct function
     *
     * @param object $linkCollection link collection
     * @param int    $itemId         item id
     *
     * @return boolean
     */
    private function _isChildProduct($linkCollection, $itemId)
    {
        $found = false;
        foreach ($linkCollection->getData() as $linkProduct) {
            if ($linkProduct['linked_product_id'] == $itemId) {
                $found = true;
                break;
            }
        }
        return $found;
    }
}
