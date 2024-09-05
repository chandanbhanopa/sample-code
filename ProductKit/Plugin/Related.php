<?php
/**
 * Helm
 *
 * This file extending the related product feature.
 *
 * This source file is subject to the Helm.
 * This is used to register the module
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

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related as RelatedParent;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\API\ProductRepositoryInterface;
use Helm\ProductKit\Helper\Data;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Related
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */
class Related extends RelatedParent
{
    const GROUP_RELATED = 'related';
    const DATA_SCOPE_CUSTOMLINKED = 'customlinked';
    protected $priceModifier;
    protected $product;
    protected $data;

    /**
     * Locator
     *
     * @var   LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * Urlinterface
     *
     * @var   UrlInterface
     * @since 101.0.0
     */
    protected $urlBuilder;

    /**
     * ProductLinkRepositoryInterface
     *
     * @var   ProductLinkRepositoryInterface
     * @since 101.0.0
     */
    protected $productLinkRepository;

    /**
     * ProductRepositoryInterface
     *
     * @var   ProductRepositoryInterface
     * @since 101.0.0
     */
    protected $productRepository;

    /**
     * Image Helper
     *
     * @var   ImageHelper
     * @since 101.0.0
     */
    protected $imageHelper;

    /**
     * Status
     *
     * @var   Status
     * @since 101.0.0
     */
    protected $status;

    /**
     * AttributeSetRepositoryInterface
     *
     * @var   AttributeSetRepositoryInterface
     * @since 101.0.0
     */
    protected $attributeSetRepository;

    /**
     * ScopeName
     *
     * @var   $scopeName
     * @since 101.0.0
     */
    protected $scopeName;

    /**
     * ScopePrefix
     *
     * @var   $scopePrefix
     * @since 101.0.0
     */
    protected $scopePrefix;

    /**
     * Constructor Function
     *
     * @param LocatorInterface                $locator                locator
     * @param UrlInterface                    $urlBuilder             urlbuilder
     * @param ProductLinkRepositoryInterface  $productLinkRepository  productlinkrepository
     * @param ProductRepositoryInterface      $productRepository      product repository
     * @param ImageHelper                     $imageHelper            image helper
     * @param Status                          $status                 status
     * @param AttributeSetRepositoryInterface $attributeSetRepository attribute set repository
     * @param Data                            $data                   data helper
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ProductLinkRepositoryInterface $productLinkRepository,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        Status $status,
        AttributeSetRepositoryInterface $attributeSetRepository,
        Data $data
    ) {

        parent::__construct(
            $locator,
            $urlBuilder,
            $productLinkRepository,
            $productRepository,
            $imageHelper,
            $status,
            $attributeSetRepository
        );

        $this->data = $data;
    }
    /**
     * AfterModifyData
     *
     * @param object $modify modify object
     * @param object $result result
     *
     * @return void
     */
    public function afterModifyMeta($modify, $result)
    {
       
        if (!$this->data->isEnable()) {
            return $result;
        }
        
        if (isset($result[static::GROUP_RELATED]['children'])) {
            $result[static::GROUP_RELATED]['children'][$modify->scopePrefix . static::DATA_SCOPE_CUSTOMLINKED] = $this->getCustomlinkedFieldset($modify);
            $result[static::GROUP_RELATED]['arguments']['data']['config']['label'] =__('Child Products, Related Products, Up-Sells, and Cross-Sells');
            $result[static::GROUP_RELATED]['arguments']['data']['config']['sortOrder'] = 10;
            
        }
        return $result;
    }

    /**
     * GetPriceModifier
     *
     * @param mixed $modify modify data object
     *
     * @return     \Magento\Catalog\Ui\Component\Listing\Columns\Price
     * @deprecated 101.0.0
     */
    private function getPriceModifier($modify)
    {
        if (!$this->priceModifier) {
            $this->priceModifier = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Catalog\Ui\Component\Listing\Columns\Price::class
            );
        }
        return $this->priceModifier;
    }

    /**
     * Prepares config for the Related products fieldset
     *
     * @param mixed $modify modify the data
     *
     * @return array
     * @since  101.0.0
     */
    protected function getCustomlinkedFieldset($modify)
    {
        $content = __(
            'Child Products Configuration'
        );
        return [
            'children' => [
                'button_set' => $modify->getButtonSet(
                    $content,
                    __('Add Child Product'),
                    $modify->scopePrefix . static::DATA_SCOPE_CUSTOMLINKED
                ),
                'modal' => $this->getGenericModal(
                    __('Add Child Product'),
                    $modify->scopePrefix . static::DATA_SCOPE_CUSTOMLINKED,
                    "chandan Bhanopa"
                ),
                static::DATA_SCOPE_CUSTOMLINKED => $this->getGrid($modify->scopePrefix . static::DATA_SCOPE_CUSTOMLINKED),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Child Products'),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 10,
                    ],
                ],
            ]
        ];
    }

    /**
     * AfetModifyData function
     *
     * @param object $modify modify data object
     * @param object $data   data
     *
     * @return void
     */
    public function afterModifyData($modify, $data)
    {
        $product = $modify->locator->getProduct();
        $newChild = [];
        $childProducts = "";
        if ($product->getChildProducts()) {
            $childProducts = json_decode($product->getChildProducts() ?? '', true);
        }
        
        if ($childProducts) {
            foreach ($childProducts as $childProduct) {
                $newChild[$childProduct['id']] = $childProduct['quantity'];
            }
        }
       
        $productId = $product->getId();

        if (!$productId) {
            return $data;
        }
        $priceModifier = $this->getPriceModifier($modify);
        /**
         * Set field name for modifier
         */
        $priceModifier->setData('name', 'price');
        $dataScopes = $this->getDataScopes();
        $dataScopes[] = static::DATA_SCOPE_CUSTOMLINKED;
       
        foreach ($dataScopes as $dataScope) {
            if ($dataScope == static::DATA_SCOPE_CUSTOMLINKED) {
                $data[$productId]['links'][$dataScope] = [];
                foreach ($modify->productLinkRepository->getList($product) as $linkItem) {
                   
                    if ($linkItem->getLinkType() !== $dataScope) {
                        continue;
                    }
                    $linkedProduct = $modify->productRepository->get(
                        $linkItem->getLinkedProductSku(),
                        false,
                        $modify->locator->getStore()->getId()
                    );
                    $productData = $this->fillData($linkedProduct, $linkItem);
                    
                    if (isset($newChild[$linkedProduct->getId()])) {
                        $productData['quantity'] = $newChild[$linkedProduct->getId()];
                        $data[$productId]['links'][$dataScope][] = $productData;
                    }
                    
                }

                if (!empty($data[$productId]['links'][$dataScope])) {
                    $dataMap = $priceModifier->prepareDataSource(
                        [
                        'data' => [
                            'items' => $data[$productId]['links'][$dataScope]
                        ]
                        ]
                    );
                    $data[$productId]['links'][$dataScope] = $dataMap['data']['items'];
                }
            }
        }
       
        return $data;
    }

    /**
     * BeforeGetLinkedProducts function
     *
     * @param object $provider provider
     * @param object $product  product
     *
     * @return void
     */
    public function beforeGetLinkedProducts($provider, $product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->product = $objectManager->create(\Helm\ProductKit\Model\Product::class);
        $currentProduct = $this->product->load($product->getId());
        return [$currentProduct];
    }

     /**
      * Retrieve meta column
      *
      * @return array
      * @since  101.0.0
      */
    protected function fillMeta()
    {
        
        return [
            'id' => $this->getTextColumn('id', false, __('ID'), 0),
            'name' => $this->getTextColumn('name', false, __('Name'), 20),
            'status' => $this->getTextColumn('status', true, __('Status'), 30),
            'attribute_set' => $this->getTextColumn('attribute_set', false, __('Attribute Set'), 40),
            'sku' => $this->getTextColumn('sku', true, __('SKU'), 50),
            'price' => $this->getTextColumn('price', true, __('Price'), 60),
            'quantity'=>[
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Input::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => 'quantity',
                            'fit' => true,
                            'label' => __('Quantity'),
                            'sortOrder' => 70,
                            'validation' => [
                                'required-entry' => true,
                                'validate-greater-than-zero' => true,
                                'validate-number' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 80,
                            'fit' => true,
                        ],
                    ],
                ],
            ]
            
        ];
    }

    /**
     * Prepare data column
     *
     * @param ProductInterface     $linkedProduct linked products
     * @param ProductLinkInterface $linkItem      linked items
     *
     * @return array
     * @since  101.0.0
     */
    protected function fillData(ProductInterface $linkedProduct, ProductLinkInterface $linkItem)
    {
            return [
                'id' => $linkedProduct->getId(),
                'thumbnail' => $this->imageHelper->init($linkedProduct, 'product_listing_thumbnail')->getUrl(),
                'name' => $linkedProduct->getName(),
                'status' => $this->status->getOptionText($linkedProduct->getStatus()),
                'attribute_set' => $this->attributeSetRepository
                    ->get($linkedProduct->getAttributeSetId())
                    ->getAttributeSetName(),
                'sku' => $linkItem->getLinkedProductSku(),
                'price' => $linkedProduct->getPrice(),
                'position' => $linkItem->getPosition()
            ];
    }
}
