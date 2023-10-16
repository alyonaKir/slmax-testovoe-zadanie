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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Block\Adminhtml;

use Bss\SeoAltText\Helper\Data;

/**
 * Class Album
 * @package Bss\SeoAltText\Block\Adminhtml
 */
class Album extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $storeFromHtml = '';
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    public $storeGroupFactory;

    /**
     * @var array
     */
    public $storeObject = [];
    /**
     * @var \Bss\SeoAltText\Helper\ProductHelper
     */
    private $productHelper;
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * Album constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param Data $dataHelper
     * @param \Magento\Store\Model\GroupFactory $storeGroupFactory
     * @param \Bss\SeoAltText\Helper\ProductHelper $productHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        Data $dataHelper,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Bss\SeoAltText\Helper\ProductHelper $productHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->productHelper = $productHelper;
        $this->storeGroupFactory = $storeGroupFactory;
        $this->websiteFactory = $websiteFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }
    /**
     * @return \Bss\SeoAltText\Helper\ProductHelper
     */
    public function getProductHelper()
    {
        return $this->productHelper;
    }
    /**
     * @return string
     */
    public function getGalleriesLink()
    {
        return $this->getUrl('bss_alt_text/process/galleries');
    }

    /**
     * @return string
     */
    public function getImageProcessLink()
    {
        return $this->getUrl('bss_alt_text/process/product');
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteCollection()
    {
        $collection = $this->websiteFactory->create()->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if ($websiteIds !== null) {
            $collection->addIdFilter($this->getWebsiteIds());
        }
        return $collection->load();
    }

    /**
     * @param object $website
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function getGroupCollection($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = $this->websiteFactory->create()->load($website);
        }
        return $website->getGroupCollection();
    }

    /**
     * @param object $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->storeGroupFactory->create()->load($group);
        }
        $stores = $group->getStoreCollection();
        $storeIds = $this->getStoreIds();
        if (!empty($storeIds)) {
            $stores->addIdFilter($storeIds);
        }
        return $stores;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChooseFromStoreHtml()
    {
        if (!$this->storeFromHtml) {
            $this->storeFromHtml = '<select ' .
                'class="admin__control-select" ' .
                'name="copy_to_stores[__store_identifier__]" ' .
                ' v-model="dataFilters.store" @change="handleChangeFilters()">';
            $this->storeFromHtml .= '<option value="0">' . __('Default Values') . '</option>';
            $dataToAdd = [
                'value' => "0",
                'label' => __("Default Values")
            ];
            $this->storeObject[] = $dataToAdd;
            foreach ($this->getWebsiteCollection() as $website) {
                $optGroupLabel = $this->escapeHtml($website->getName());
                $this->storeFromHtml .= '<optgroup label="' . $optGroupLabel . '"></optgroup>';
                foreach ($this->getGroupCollection($website) as $group) {
                    $optGroupName = $this->escapeHtml($group->getName());
                    $this->storeFromHtml .= '<optgroup label="---' . $optGroupName . '">';
                    foreach ($this->getStoreCollection($group) as $store) {
                        $this->storeFromHtml .= '<option value="' . $store->getId() . '">&nbsp;&nbsp;&nbsp;&nbsp;';
                        $this->storeFromHtml .= $this->escapeHtml($store->getName()) . '</option>';
                        $dataToAdd = [
                            'value' => $store->getId(),
                            'label' => $this->escapeHtml($store->getName())
                        ];
                        $this->storeObject[] = $dataToAdd;
                    }
                }
                $this->storeFromHtml .= '</optgroup>';
            }
            $this->storeFromHtml .= '</select>';
        }
        return $this->storeFromHtml;
    }
}
