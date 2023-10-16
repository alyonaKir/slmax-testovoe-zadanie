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
 * @package    Bss_Redirects301Seo
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Redirects301Seo\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ProductSavingObserverDeleted
 *
 * @package Bss\Redirects301Seo\Observer
 */
class ProductSavingObserverDeleted implements ObserverInterface
{
    /**
     * @var \Bss\Redirects301Seo\Helper\SaveUrlKeyDeleted
     */
    protected $saveUrlKeyDeleted;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Bss\Redirects301Seo\Block\Redirect301Seo
     */
    protected $redirect301Seo;

    /**
     * @var \Bss\Redirects301Seo\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productLoader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * ProductSavingObserverDeleted constructor.
     * @param \Bss\Redirects301Seo\Helper\SaveUrlKeyDeleted $saveUrlKeyDeleted
     * @param \Magento\Catalog\Model\ProductFactory $productLoader
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Bss\Redirects301Seo\Helper\Data $dataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\Redirects301Seo\Block\Redirect301Seo $redirect301Seo
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     */
    public function __construct(
        \Bss\Redirects301Seo\Helper\SaveUrlKeyDeleted $saveUrlKeyDeleted,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Bss\Redirects301Seo\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\Redirects301Seo\Block\Redirect301Seo $redirect301Seo
    ) {
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->redirect301Seo = $redirect301Seo;
        $this->date = $date;
        $this->productLoader = $productLoader;
        $this->saveUrlKeyDeleted = $saveUrlKeyDeleted;
    }

    /**
     * @inheritDoc
     */
    public function execute(EventObserver $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $getEnable = $this->dataHelper->getRedirectsEnable($storeId);
        if ($getEnable) {
            $date = $this->date->gmtDate();
            $productCollection = $observer->getProduct();
            $idProduct = $productCollection->getId();
            $pathProduct = [];
            $pathProduct[] = $this->redirect301Seo->getCanonicalUrlPath($productCollection, null);

            $productWebsiteIds = $productCollection->getWebsiteIds();
            $stores = $this->storeManager->getStores(false);

            foreach ($stores as $store) {
                $storeId = $store->getId();
                $websiteId = $store->getWebsiteId();
                if (in_array($websiteId, $productWebsiteIds)) {
                    $productObject = $this->productLoader->create()->setStoreId($storeId)->load($idProduct);
                    $pathProductStore = $this->redirect301Seo->getCanonicalUrlPath($productObject, null);
                    if (!in_array($pathProductStore, $pathProduct)) {
                        $pathProduct[] = $pathProductStore;
                    }
                }
            }

            $categoryIds = $productCollection->getCategoryIds();
            $categoryIdsString = implode('-', $categoryIds);
            if ($pathProduct !== []) {
                foreach ($pathProduct as $pathProductChild) {
                    $this->saveUrlKeyDeleted->saveUrlValue($pathProductChild, $idProduct, $date, $categoryIdsString);
                }
            }
        }
    }
}
