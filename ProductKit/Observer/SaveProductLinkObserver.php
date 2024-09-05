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
namespace Helm\ProductKit\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Helm\ProductKit\Helper\Data;
use Magento\Framework\Message\ManagerInterface;

/**
 * SaveProductLinkObserver Class
 *
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 * @api
 * @since     101.0.0
 */
class SaveProductLinkObserver implements ObserverInterface
{
    /**
     * Request class object
     *
     * @var Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Product kit helper object
     *
     * @var Helm\ProductKit\Helper\Data
     */
    protected $productKitHelper;

    protected $messageManager;
    
    /**
     * Constructor function
     *
     * @param RequestInterface $request          request
     * @param Data             $productKitHelper helper
     */
    public function __construct(
        RequestInterface $request,
        Data $productKitHelper,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->productKitHelper = $productKitHelper;
        $this->messageManager = $messageManager;
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
        $requestedProduct = $observer->getEvent()->getDataObject();
        $existingProductKit = [];
        $newProducts = [];
        $post = $this->request->getPostValue();
        if (isset($post['links']['customlinked']) && $post['product']['product_kit']) {
            $productKits = $post['links']['customlinked'];
            foreach ($productKits as $product) {
                $checkProductKit  = $this->checkIsProductKit($product['id']);
                if ($checkProductKit->getChildProducts()) {
                    $existingProductKit[] = $checkProductKit->getName();
                } else {
                    $newProducts[] = $product;
                }
            }

            if ($existingProductKit) {
                $existingProductKitString =  implode(", ", $existingProductKit);
                $this->messageManager->addErrorMessage(__($existingProductKitString. " already belongs to other kit"));
            }
            if ($newProducts) {
                $childProducts = json_encode($newProducts);
                $requestedProduct->setChildProducts($childProducts);
            }
            
        } else {
            $requestedProduct->setChildProducts("");
        }
    }

    /**
     * Get Product detials
     *
     * @param int $productId
     * @return object
     */
    private function checkIsProductKit($productId)
    {
        return $this->productKitHelper->getProductData($productId);
    }
}
