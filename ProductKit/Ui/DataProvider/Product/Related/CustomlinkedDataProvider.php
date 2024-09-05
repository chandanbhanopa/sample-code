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
namespace Helm\ProductKit\Ui\DataProvider\Product\Related;

use Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Helm\ProductKit\Helper\Data as ProductKitHelper;
use Magento\Framework\App\Request\Http;
use Laminas\Uri\Http as HttpCore;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Data Class
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 * @api
 * @since     101.0.0
 */
class CustomlinkedDataProvider extends AbstractDataProvider
{

     /**
      * @var RequestInterface
      * @since 101.0.0
      */
    protected $request;

    /**
     * @var ProductRepositoryInterface
     * @since 101.0.0
     */
    protected $productRepository;

    /**
     * @var StoreRepositoryInterface
     * @since 101.0.0
     */
    protected $storeRepository;

    /**
     * @var ProductLinkRepositoryInterface
     * @since 101.0.0
     */
    protected $productLinkRepository;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var ProductKitHelper
     */
    protected $productKitHelper;

    /**
     * @var Http
     */
    protected $http;

    /**
     *
     * @var HttpCore
     */
    protected $httpCore;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository,
        ProductLinkRepositoryInterface $productLinkRepository,
        $addFieldStrategies,
        $addFilterStrategies,
        ProductKitHelper $productKitHelper,
        Http $http,
        HttpCore $httpCore,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $request,
            $productRepository,
            $storeRepository,
            $productLinkRepository,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
        $this->productLinkRepository = $productLinkRepository;
        $this->productKitHelper = $productKitHelper;
        $this->http = $http;
        $this->httpCore =  $httpCore;
    }

    /**
     * {@inheritdoc}
     *
     * @since 101.0.0
     *
     * @return string
     */
    protected function getLinkType()
    {
        return 'customlinked';
    }

    /**
     * GetCollection method
     *
     * {@inheritdoc}
     *
     * @since 101.0.0
     *
     * @return object | array
     */
    public function getCollection()
    {
        $collection = parent::getCollection();
        $kitProducts = $this->productKitHelper->getLinkedProductCollection();
        $storeId = 0;
        $urlData =  $this->httpCore->parse($this->http->getServer('HTTP_REFERER'));
   
        $searchStringStore = "store/";
        $searchStringEdit = "/back/edit/";
        
        if (strpos($urlData, $searchStringStore) !== false) {
            $urlPathData = explode($searchStringStore, $urlData);
            array_shift($urlPathData);
            if (strpos($urlPathData[0], "/key/") !== false) {
                $b = explode("/key/", $urlPathData[0]);
                $storeId = array_shift($b);
            }
            
            if (strpos($urlPathData[0], $searchStringEdit) !== false) {
                $b = explode($searchStringEdit, $urlPathData[0]);
                $storeId = array_shift($b);
            }

            if (strpos($urlPathData[0], "/") !== false) {
                $c = explode("/", $urlPathData[0]);
                $storeId = array_shift($c);
            }
            
        }
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToFilter('type_id', ['eq' => Type::TYPE_SIMPLE]);
        $collection->addAttributeToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]);
        $collection->addAttributeToFilter('status', ['eq' => Status::STATUS_ENABLED]);
        $collection->addAttributeToFilter('is_cobranding', ['eq' => 0]);
        $collection->addFieldToFilter('entity_id', [['nin' => $kitProducts]]);
        $collection->addStoreFilter($storeId);

        if (!$this->getProduct()) {
            return $collection;
        }
        
        $collection->addAttributeToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getProduct()->getId()]]
        );
        return $this->addCollectionFilters($collection);
    }
}
