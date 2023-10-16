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
 * @package    Bss_MultiStoreViewPricingTierPrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingTierPrice\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;

class Tierprice extends \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\MultiStoreViewPricingTierPrice\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    protected $resourceTierprice;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $productAttributeTierprice
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param \Bss\MultiStoreViewPricingTierPrice\Model\ResourceModel\Product\Attribute\Backend\Tierprice $resourceTierprice
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param ScopeOverriddenValue|null $scopeOverriddenValue
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $productAttributeTierprice,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Bss\MultiStoreViewPricingTierPrice\Model\ResourceModel\Product\Attribute\Backend\Tierprice $resourceTierprice,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        ScopeOverriddenValue $scopeOverriddenValue = null
    ) {
        $this->helper = $helper;
        $this->resourceTierprice = $resourceTierprice;
        $this->eavAttribute = $eavAttribute;

        parent::__construct(
            $currencyFactory,
            $storeManager,
            $catalogData,
            $config,
            $localeFormat,
            $catalogProductType,
            $groupManagement,
            $productAttributeTierprice,
            $scopeOverriddenValue
        );
    }

    /**
     * {@inheritdoc}
     */
    public function afterLoad($object)
    {
        if (!$this->helper->isScopePrice()) {
            return parent::afterLoad($object);
        }

        //dont apply in product manager admin
        if ($this->helper->isAdmin() && $this->helper->checkRoute() == 'catalog_product') {
            return parent::afterLoad($object);
        }

        $storeId = $object->getStoreId();
        $websiteId = null;
        if ($this->getAttribute()->isScopeGlobal()) {
            $websiteId = 0;
        } elseif ($storeId) {
            $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        }

        $data = $this->_getResource()->loadPriceData(
            $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()),
            $websiteId
        );

        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = $this->_groupManagement->getAllCustomersGroup()->getId();
            }
        }

        // get tier price for store
        if ($this->helper->getTierPriceConfig() == 1) {
            $data = [];
        }

        $tierDatas = $this->resourceTierprice->loadPriceData(
            $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()),
            $storeId
        );
        foreach ($tierDatas as $k => $v) {
            unset($tierDatas[$k]['store_id']);
            $tierDatas[$k]['website_id'] = $websiteId;
            $tierDatas[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $tierDatas[$k]['cust_group'] = $this->_groupManagement->getAllCustomersGroup()->getId();
            }
            $data[] = $tierDatas[$k];
        }
        // end

        $data = $this->modifyPriceData($object, $data);

        if (!$object->getData('_edit_mode') && $websiteId) {
            $data = $this->preparePriceData($data, $object->getTypeId(), $websiteId);
        }

        // sort by price_qty
        usort($data, function ($a, $b) {
            $a = $a['price_qty'];
            $b = $b['price_qty'];

            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
        // end

        // remove unused items
        $current = [];
        foreach ($data as $key => $price) {
            if (empty($current)) {
                $current = $data[$key];
                continue;
            }

            if ($price['website_price'] > $current['website_price']) {
                unset($data[$key]);
            } else {
                $current = $data[$key];
            }
        }
        // end

        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Prepare group prices data for website
     *
     * @param array $priceData
     * @param string $productTypeId
     * @param int $websiteId
     * @return array
     */
    public function preparePriceData(array $priceData, $productTypeId, $websiteId)
    {
        $rates = $this->_getWebsiteCurrencyRates();
        $data = [];
        $price = $this->_catalogProductType->priceFactory($productTypeId);
        foreach ($priceData as $v) {
            if (!array_filter($v)) {
                continue;
            }
            $key = implode('-', array_merge([$v['cust_group']], $this->_getAdditionalUniqueFields($v)));
            if ($v['website_id'] == $websiteId || $v['website_id'] == '0') {
                if (isset($data[$key])) {
                    if ($v['price'] > $data[$key]['website_price']) {
                        continue;
                    }
                }

                $data[$key] = $v;
                $data[$key]['website_price'] = $v['price'];
            } elseif ($v['website_id'] == 0 && !isset($data[$key])) {
                $data[$key] = $v;
                $data[$key]['website_id'] = $websiteId;
                if ($this->_isPriceFixed($price)) {
                    $data[$key]['price'] = $v['price'] * $rates[$websiteId]['rate'];
                    $data[$key]['website_price'] = $v['price'] * $rates[$websiteId]['rate'];
                }
            }
        }

        return $data;
    }
}
