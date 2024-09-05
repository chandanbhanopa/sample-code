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
declare(strict_types=1);

namespace Helm\ProductKit\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote;

/**
 * Data Class Helps to use reuse logic
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class Data extends AbstractHelper
{

    public const XML_MODULE_ENABLE = "helm_product_kit/general/enable";

    /**
     * Scope config interface
     *
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Product Repository Data
     *
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Productlink Collection to get the product
     *
     * @var Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory
     */
    protected $productLinkCollection;

    /**
     * StockItemRepository Data
     *
     * @var Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * Store manager helps to get stock data
     *
     * @var Magento\Store\Model\StoreManagerInterface;
     */
    protected $storeManager;

    /**
     * Checkout Session
     *
     * @var Session;
     */
    protected $checkoutSession;

    /**
     * Product Collection
     *
     * @var ProductCollection;
     */
    protected $productCollection;

    private $quote;

    private $cart;

    /**
     * Constructor function
     *
     * @param ProductRepositoryInterface $productRepository     product repository
     * @param CollectionFactory          $productLinkCollection product link collection
     * @param StockItemRepository        $stockItemRepository   stock item repository
     * @param ScopeConfigInterface       $scopeConfig           scope config
     * @param StoreManagerInterface      $storeManager          store manager
     * @param Session                    $checkoutSession       checkout session
     * @param ProductCollection          $productCollection     Product Collection
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CollectionFactory $productLinkCollection,
        StockItemRepository $stockItemRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        ProductCollection $productCollection,
        Quote $quote,
        Cart $cart
    ) {
        $this->productRepository = $productRepository;
        $this->productLinkCollection =  $productLinkCollection;
        $this->stockItemRepository = $stockItemRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->productCollection = $productCollection;
        $this->quote = $quote;
        $this->cart = $cart;
    }

    /**
     * Check Module is enable or disable
     *
     * @return boolean
     */
    public function isEnable()
    {
        // Get the value of the XML_MODULE_ENABLE configuration path
        $isEnable = $this->scopeConfig->getValue(
            self::XML_MODULE_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getStoreId()
        );
       
        return (bool) $isEnable;
    }

    /**
     * Filter Child Product function
     *
     * @param object $product product
     *
     * @return void
     */
    public function filterChildProducts($product)
    {
        $childProductJson = $product->getChildProducts();
        $childProducts = [];
        if ($childProductJson) {
            $childProducts = json_decode($childProductJson ?? '', true);
        }
        return $childProducts;
    }

    /**
     * GetProductData function
     *
     * @param int $productId product id
     *
     * @return void
     */
    public function getProductData($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Filter Child product for cart function
     *
     * @param object $product Product
     *
     * @return void
     */
    public function filterChildProductsForCart($product)
    {
        $childProductJson = $product->getChildProducts();
        $childProducts = [];
        if ($childProductJson) {
            $childProducts = json_decode($childProductJson ?? '', true);
        }

        $cartProducts = [];
        foreach ($childProducts as $key => $chilProduct) {
            $cartProducts[$product->getId()][] = ['id'=> $chilProduct['id'], 'quantity'=> $chilProduct['quantity']];
        }
        return $cartProducts;
    }

    /**
     * Check product is child of other product (Product Kit)
     *
     * @param int $productId product id
     *
     * @return boolean true | false
     */
    public function checkProductIsChild($productId)
    {

        $productKitId = \Helm\ProductKit\Model\Product::LINK_TYPE_CUSTOMLINKED;
        $productLinkCollection = $this->productLinkCollection->create();
        $productLinkCollection->getSelect()->joinInner(
            ['link' => "catalog_product_link"],
            'e.entity_id = link.linked_product_id'
        )->where("link.link_type_id = ".$productKitId)
        ->where("link.linked_product_id = ".$productId);
        $found = false;
        foreach ($productLinkCollection->getData() as $linkProduct) {
            if ($linkProduct['linked_product_id'] == $productId) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    /**
     * Check Product Stock
     *
     * @param int $productId product id
     *
     * @return object
     */
    public function checkStock($productId)
    {
        $product =  $this->getProductData($productId);
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        return $stockItem;
    }

    /**
     * Store data
     *
     * @return object
     */
    public function getStoreData()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Product Kit collection
     *
     * @return array $childProductCollection
     */
    public function getLinkedProductCollection()
    {

        $productKitId = \Helm\ProductKit\Model\Product::LINK_TYPE_CUSTOMLINKED;
        $productLinkCollection = $this->productLinkCollection->create();
        $productLinkCollection->getSelect()->joinInner(
            ['link' => "catalog_product_link"],
            'e.entity_id = link.linked_product_id'
        )->where("link.link_type_id = ".$productKitId);

        $childProductCollection = [];
        if ($productLinkCollection->getData()) {
            foreach ($productLinkCollection->getData() as $data) {
                $childProductCollection[] = $data['linked_product_id'];
            }
        }

        return $childProductCollection;
    }

    /**
     * Find the parent product of cart product
     *
     * @param int $productId product id
     *
     * @return boolean true | false
     */
    public function findParentProductInCart($childProductId)
    {
        $parentProductId = 0;
        $productKitId = \Helm\ProductKit\Model\Product::LINK_TYPE_CUSTOMLINKED;
        $productLinkCollection = $this->productLinkCollection->create();
        $productLinkCollection->getSelect()->joinInner(
            ['link' => "catalog_product_link"],
            'e.entity_id = link.linked_product_id'
        )->where("link.link_type_id = ".$productKitId)
        ->where("link.linked_product_id = ".$childProductId);

        $parentProductInCart = 0;
        foreach ($productLinkCollection->getData() as $linkProduct) {
            
            if ($linkProduct['linked_product_id'] == $childProductId) {
                
                $productCollection = $this->productCollection->create();
                $productCollection->addAttributeToFilter('row_id', ['eq'=> $linkProduct['product_id']]);
                $parentProductId = $productCollection->getFirstItem()->getId();
               
            }
        }

        return $parentProductId;
    }

    /**
     * Find the parent product of cart product
     *
     * @param int $productId product id
     *
     * @return boolean true | false
     */
    public function isShowAction($parentProductId)
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote->hasProductId($parentProductId);

    }
}
