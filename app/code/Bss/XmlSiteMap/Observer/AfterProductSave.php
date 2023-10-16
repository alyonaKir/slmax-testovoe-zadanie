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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Observer;

use Bss\XmlSiteMap\Helper\Data as DataHelper;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AfterProductSave
 * @package Bss\XmlSiteMap\Observer
 */
class AfterProductSave implements ObserverInterface
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;
    /**
     * @var \Bss\XmlSiteMap\Helper\Data
     */
    private $dataHelper;
    /**
     * @var CacheTypeListInterface
     */
    private $cache;

    /**
     * AfterCategorySave constructor.
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param CacheTypeListInterface $cache
     * @param DataHelper $dataHelper
     */
    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        CacheTypeListInterface $cache,
        DataHelper $dataHelper
    ) {
        $this->cache = $cache;
        $this->dataHelper = $dataHelper;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $productObject = $observer->getEvent()->getProduct();
        $storeId = $productObject->getStoreId();

        $statusModule = $this->dataHelper->isEnableModule();
        $productDisable = $this->dataHelper->getConfigWithoutCache(
            $storeId,
            DataHelper::XML_PATH_PRODUCT_ID_INCLUDE
        );
        if ($productDisable) {
            $productDisableArray = explode(',', $productDisable);
        } else {
            $productDisableArray = [];
        }
        $this->processSaveConfig($productObject, $statusModule, $productDisableArray);
        return $this;
    }

    /**
     * @param object $productObject
     * @param bool $statusModule
     * @param array $productDisableArray
     */
    public function processSaveConfig($productObject, $statusModule, $productDisableArray)
    {
        $storeId = $productObject->getStoreId();
        $productId = (string)$productObject->getId();
        $excludedXmlSiteMap = $productObject->getExcludedXmlSitemap();
        if ($statusModule && (int)$excludedXmlSiteMap === 1) {
            if (!in_array($productId, $productDisableArray)) {
                $productDisableArray[] = $productId;
                $this->cache->invalidate('full_page');
            }
        }
        if ($statusModule && (int)$excludedXmlSiteMap === 0 && in_array($productId, $productDisableArray)) {
            $arrayKey = array_search($productId, $productDisableArray);
            if ($arrayKey !== false) {
                unset($productDisableArray[$arrayKey]);
                $this->cache->invalidate('full_page');
            }
        }
        $finalProductDisable = implode(',', $productDisableArray);
        $scopeToAdd = ScopeInterface::SCOPE_STORES;
        if ((int)$storeId === 0) {
            $scopeToAdd = 'default';
        }
        $this->saveNewConfig(
            DataHelper::XML_PATH_PRODUCT_ID_INCLUDE,
            $finalProductDisable,
            $scopeToAdd,
            $storeId
        );
    }

    /**
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string $scopeId
     * @return \Magento\Config\Model\ResourceModel\Config
     */
    protected function saveNewConfig($path, $value, $scope = 'default', $scopeId = '')
    {
        return $this->resourceConfig->saveConfig($path, $value, $scope, $scopeId);
    }
}
