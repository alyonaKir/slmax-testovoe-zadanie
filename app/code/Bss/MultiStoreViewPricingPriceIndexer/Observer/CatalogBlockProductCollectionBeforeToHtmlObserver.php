<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiStoreViewPricingPriceIndexer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingPriceIndexer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogBlockProductCollectionBeforeToHtmlObserver implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver
     */
    private $reviewBeforeToHtmlObserver;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $reviewBeforeToHtmlObserver
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $reviewBeforeToHtmlObserver,
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->reviewBeforeToHtmlObserver = $reviewBeforeToHtmlObserver;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        $productCollectionClass = \Magento\Catalog\Model\ResourceModel\Product\Collection::class;
        if ($this->helper->isScopePrice() && $productCollection instanceof $productCollectionClass) {
            $storeId = $this->storeManager->getStore()->getId();
            $productCollection->getSelect()->where('price_index.store_id = ?', $storeId);
        }

        try {
            $this->reviewBeforeToHtmlObserver->execute($observer);
        } catch (\Exception $e) {
            // Review module is not available
        }

        return $this;
    }
}
