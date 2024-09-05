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
class RemoveChildProductFromCart implements ObserverInterface
{
    protected $cartRepository;

    /**
     * Constructor function
     *
     * @param CartRepositoryInterface $cartRepository cart repository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Excecute function
     *
     * @param Observer $observer observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();
        $product = $quoteItem->getProduct();
        
        $childProductJson = $product->getChildProducts();
        if ($childProductJson) {
            $childProductDataArray = json_decode($childProductJson ?? '', true);
            foreach ($childProductDataArray as $childProductData) {
                $childProductId = $childProductData['id'];
                $quote = $quoteItem->getQuote();
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getProductId() == $childProductId) {
                        $quote->removeItem($item->getId());
                    }
                }
            }
            $this->cartRepository->save($quote);
        }
    }
}
