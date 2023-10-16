<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Helper;

/**
 * Class Data
 * @package Bss\ProductLabel\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\ProductLabel\Model\LabelFactory
     */
    private $labelFatory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Bss\ProductLabel\Model\LabelFactory $labelFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Bss\ProductLabel\Model\LabelFactory $labelFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->labelFatory = $labelFactory;
        $this->customerSession = $customerSession;
        $this->timezone = $timezone;
        $this->stockRegistry = $stockRegistry;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'productlabel/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isDisplayMultipleLabel()
    {
        return $this->scopeConfig->isSetFlag(
            'productlabel/general/display_multiple_label',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnableOnlyOutOfStockLabel()
    {
        return $this->scopeConfig->isSetFlag(
            'productlabel/general/display_only_out_of_stock_label',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function isNotDisplayOn()
    {
        $display = $this->scopeConfig->getValue(
            'productlabel/general/not_display_label_on',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $display;
    }

    /**
     * @param \Bss\ProductLabel\Model\Config\Source\PageDisplayLabel $page
     * @return bool
     */
    public function isSystemConfigAllow($page)
    {
        $pos = strpos($this->isNotDisplayOn(), $page);

        return ( ($this->isEnable() == true) && ($pos === false) );
    }

    /**
     * @return string
     */
    public function getSelectorProductList()
    {
        $selector = $this->scopeConfig->getValue(
            'productlabel/display/product_list',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $selector;
    }

    /**
     * @return string
     */
    public function getSelectorProductPage()
    {
        $selector = $this->scopeConfig->getValue(
            'productlabel/display/product_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $selector;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManagerInterface->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        return $mediaUrl;
    }

    /**
     * Get label data by product attribute 'label_data'
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLabelData($product)
    {
        $labelData = [];

        // Check product is out of stock
        $outStockLabel = $this->getOutOfStockLabelDate($product);

        if (!empty($outStockLabel)) {
            if ($this->isEnableOnlyOutOfStockLabel()) {
                $labelImageData = json_decode($outStockLabel['image_data'], true);
                $labelImageData['image'] = $this->getMediaUrl() . $outStockLabel['image'];
                // avoid case of z-index is zero or smaller than product image's
                $labelImageData['priority'] = $outStockLabel['priority'] + 10;
                $labelImageData['product_id'] = $product->getId();
                return [$labelImageData];
            }
            $labelData[] = $outStockLabel;
        }

        $labelData = $this->getLabelDataProduct($product, $labelData);

        if (!empty($labelData)) {
            $labelData = $this->filterLabelData($labelData, $product->getId());
            usort($labelData, [$this, 'customUsort']);
            if (!$this->isDisplayMultipleLabel()) {
                return empty($labelData) ? [] : [$labelData[0]];
            }
        }

        return $labelData;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getOutOfStockLabelDate($product)
    {
        if ($product->getIsSalable() == 0) {
            $dateTimeZone = $this->timezone->date()->format('Y-m-d H:i:s');
            $outStockLabel = $this->labelFatory->create()->getCollection()
                ->addFieldToFilter('active', true)
                ->addFieldToFilter('apply_outofstock_product', true)
                ->addFieldToFilter('image', ['notnull' => true ])
                ->addFieldToFilter('image_data', ['notnull' => true ])
                ->addFieldToFilter('valid_start_date', [['lt' => $dateTimeZone], ['null' => true]])
                ->addFieldToFilter('valid_end_date', [['gt' => $dateTimeZone], ['null' => true ]])
                ->addFieldToFilter(
                    'store_views',
                    [
                        ['finset' => [$this->storeManagerInterface->getStore()->getId()]],
                        ['null' => true ]
                    ]
                )
                ->addFieldToFilter(
                    'customer_groups',
                    [
                        ['finset' => [$this->customerSession->getCustomerGroupId()]],
                        ['null' => true ]
                    ]
                )
                ->setOrder('priority', 'DESC')
                ->getData();
            $outStockLabel = current($outStockLabel);
            return $outStockLabel;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $labelData
     * @return array
     */
    protected function getLabelDataProduct($product, $labelData)
    {
        $newLabelData = $product->getResource()->getAttributeRawValue(
            $product->getId(),
            'label_data',
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
        );

        if (!empty($newLabelData)) {
            $newLabelData = json_decode($newLabelData, true);
            if (!empty($newLabelData)) {
                // check duplicate between "out of stock label" and "product's label existed"
                if (isset($labelData[0]) && isset($labelData[0]['id'])) {
                    $key = array_search($labelData[0]['id'], array_column($newLabelData, 'id'));
                    if (is_int($key) != false) {
                        $labelData = [];
                    }
                }
                $labelData = array_merge($labelData, $newLabelData);
            }
        }

        return $labelData;
    }

    /**
     * Label data filter
     *
     * @param array $labelDatas
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function filterLabelData($labelDatas, $productId)
    {
        $data = [];
        if (!empty($labelDatas)) {
            foreach ($labelDatas as $labelData) {
                // Check customer group/store view/date is valid
                if ($this->isCustomerGroupValid($labelData['customer_groups'])
                    && $this->checkValidDate($labelData['valid_start_date'], $labelData['valid_end_date'])
                    && $this->isStoreViewValid($labelData['store_views']) && $labelData['active'] == true) {
                    $labelImageData = json_decode($labelData['image_data'], true);
                    $labelImageData['image'] = $this->getMediaUrl() . $labelData['image'];
                    // avoid case of z-index is zero or smaller than product image's
                    $labelImageData['priority'] = $labelData['priority'] + 10;
                    $labelImageData['product_id'] = $productId;

                    $data[] = $labelImageData;
                }
            }
        }

        return $data;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function customUsort($a, $b)
    {
        if ($a['priority'] == $b['priority']) {
            return 0;
        }
        return ($a['priority'] < $b['priority']) ? 1 : -1;
    }

    /**
     * Check label valid date
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function checkValidDate($startDate, $endDate)
    {
        $dateTimeZone = $this->timezone->date()->format('Y-m-d H:i:s');
        $currentTime = strtotime($dateTimeZone);

        // checking Label is not yet display
        if (!empty($startDate) && $currentTime < strtotime($startDate)) {
            return false;
        };

        // checking Label is expired
        if (!empty($endDate) && $currentTime > strtotime($endDate)) {
            return false;
        };

        return true;
    }

    /**
     * Check customer group is valid
     * @param $inputGroups
     * @return bool
     */
    public function isCustomerGroupValid($inputGroups)
    {
        if ($inputGroups != null) {
            $inputGroupsArr = explode(',', $inputGroups);
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            return in_array($customerGroupId, $inputGroupsArr);
        }
        return true;
    }

    /**
     * Check store view is valid
     *
     * @param string $inputStore
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isStoreViewValid($inputStore)
    {
        if (!empty($inputStore)) {
            $inputGroupsArr = explode(',', $inputStore);
            $currentStore = $this->storeManagerInterface->getStore();
            $currentStoreId = $currentStore->getId();
            return in_array($currentStoreId, $inputGroupsArr);
        }
        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        return $stockItem;
    }

    /**
     * @param string $withVersion
     * @return bool
     */
    public function compareCurrentVersion($withVersion)
    {
        $current = $this->productMetadata->getVersion();
        if (version_compare($current, $withVersion) >= 0) {
            return true;
        }
        return false;
    }
}
