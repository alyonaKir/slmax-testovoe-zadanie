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
 * @package    Bss_Breadcrumbs
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Breadcrumbs\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class ChangeBlock
 *
 * @package Bss\Breadcrumbs\Observer
 */
class ChangeBlock implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Bss\Breadcrumbs\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * ChangeBlock constructor.
     * @param \Bss\Breadcrumbs\Helper\Data $dataHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Bss\Breadcrumbs\Helper\Data $dataHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get the store ID
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @inheritDoc
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getData('layout');
        $moduleEnable = $this->dataHelper->getBreadcrumbsEnable($this->getStoreId());
        $fullActionName = $observer->getData('full_action_name');

        $version = $this->productMetadata->getVersion();

        $checkVersion = version_compare($version, '2.2.2', '>=');

        if (!$checkVersion && $fullActionName == 'catalog_product_view' && $moduleEnable) {
            $layout->getUpdate()->addHandle('add_magento21_layout');
        }
        return $this;
    }
}
