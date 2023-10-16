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
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingTierPrice\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;

class Tierprice extends \Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice
{
    /**
     * Catalog product attribute backend tierprice
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    protected $_productAttributeBackendTierprice;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param GroupManagementInterface $groupManagement
     * @param \Bss\MultiStoreViewPricingTierPrice\Model\ResourceModel\Product\Attribute\Backend\Tierprice $productAttributeTierprice
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
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
        \Bss\MultiStoreViewPricingTierPrice\Model\ResourceModel\Product\Attribute\Backend\Tierprice $productAttributeTierprice,
        \Magento\Framework\App\Request\Http $request,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        ScopeOverriddenValue $scopeOverriddenValue = null
    ) {
        $this->_productAttributeBackendTierprice = $productAttributeTierprice;
        $this->request = $request;
        $this->helper = $helper;
        parent::__construct(
            $currencyFactory,
            $storeManager,
            $catalogData,
            $config,
            $localeFormat,
            $catalogProductType,
            $groupManagement,
            $scopeOverriddenValue
        );
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    protected function _getResource()
    {
        return $this->_productAttributeBackendTierprice;
    }

    /**
     * Add price qty to unique fields
     *
     * @param array $objectArray
     * @return array
     */
    protected function _getAdditionalUniqueFields($objectArray)
    {
        $uniqueFields = parent::_getAdditionalUniqueFields($objectArray);
        $uniqueFields['qty'] = $objectArray['price_qty'] * 1;
        return $uniqueFields;
    }

    /**
     * Error message when duplicates
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getDuplicateErrorMessage()
    {
        return __('We found a duplicate website, tier price, customer group and quantity.');
    }

    /**
     * Whether tier price value fixed or percent of original price
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $priceObject
     * @return bool
     */
    protected function _isPriceFixed($priceObject)
    {
        return $priceObject->isTierPriceFixed();
    }

    /**
     * By default attribute value is considered non-scalar that can be stored in a generic way
     *
     * @return bool
     */
    public function isScalar()
    {
        return false;
    }

    /**
     * Validate group price data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Phrase|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate($object)
    {
        $attribute = $this->getAttribute();
        $priceRows = $object->getData($attribute->getName());
        $priceRows = array_filter((array)$priceRows);

        if (empty($priceRows)) {
            return true;
        }

        // validate per website
        $duplicates = [];
        foreach ($priceRows as $priceRow) {
            if (!empty($priceRow['delete'])) {
                continue;
            }
            $compare = implode(
                '-',
                array_merge(
                    [$priceRow['store_id'], $priceRow['cust_group']],
                    $this->_getAdditionalUniqueFields($priceRow)
                )
            );
            if (isset($duplicates[$compare])) {
                throw new \Magento\Framework\Exception\LocalizedException(__($this->_getDuplicateErrorMessage()));
            }

            if (!$this->isPositiveOrZero($priceRow['price'])) {
                return __('Group price must be a number greater than 0.');
            }

            $duplicates[$compare] = true;
        }

        // if attribute scope is website and edit in store view scope
        // add global group prices for duplicates find
        if (!$attribute->isScopeGlobal() && $object->getStoreId()) {
            $origPrices = $object->getOrigData($attribute->getName());
            if ($origPrices) {
                foreach ($origPrices as $price) {
                    if ($price['store_id'] == 0) {
                        $compare = implode(
                            '-',
                            array_merge(
                                [$price['store_id'], $price['cust_group']],
                                $this->_getAdditionalUniqueFields($price)
                            )
                        );
                        $duplicates[$compare] = true;
                    }
                }
            }
        }

        // validate currency
        $baseCurrency = $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE, 'default');
        $rates = $this->getStoreCurrencyRates();
        foreach ($priceRows as $priceRow) {
            if (!empty($priceRow['delete'])) {
                continue;
            }
            if ($priceRow['store_id'] == 0) {
                continue;
            }

            $globalCompare = implode(
                '-',
                array_merge([0, $priceRow['cust_group']], $this->_getAdditionalUniqueFields($priceRow))
            );
            $websiteCurrency = $rates[$priceRow['store_id']]['code'];

            if ($baseCurrency == $websiteCurrency && isset($duplicates[$globalCompare])) {
                throw new \Magento\Framework\Exception\LocalizedException(__($this->_getDuplicateErrorMessage()));
            }
        }

        return true;
    }

    /**
     * Retrieve stores currency rates and base currency codes
     *
     * @return array
     */
    protected function getStoreCurrencyRates()
    {
        if (is_null($this->_rates)) {
            $this->_rates = [];
            $baseCurrency = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );
            foreach ($this->_storeManager->getStores() as $store) {
                /* @var $website \Magento\Store\Model\Website */
                if ($store->getBaseCurrencyCode() != $baseCurrency) {
                    $rate = $this->_currencyFactory->create()->load(
                        $baseCurrency
                    )->getRate(
                        $store->getBaseCurrencyCode()
                    );
                    if (!$rate) {
                        $rate = 1;
                    }
                    $this->_rates[$store->getId()] = [
                        'code' => $store->getBaseCurrencyCode(),
                        'rate' => $rate,
                    ];
                } else {
                    $this->_rates[$store->getId()] = ['code' => $baseCurrency, 'rate' => 1];
                }
            }
        }
        return $this->_rates;
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
            if ($v['store_id'] == $websiteId) {
                $data[$key] = $v;
                $data[$key]['website_price'] = $v['price'];
            } elseif ($v['store_id'] == 0 && !isset($data[$key])) {
                $data[$key] = $v;
                $data[$key]['store_id'] = $websiteId;
                if ($this->_isPriceFixed($price)) {
                    $data[$key]['price'] = $v['price'] * $rates[$websiteId]['rate'];
                    $data[$key]['website_price'] = $v['price'] * $rates[$websiteId]['rate'];
                }
            }
        }

        return $data;
    }

    /**
     * After Save Attribute manipulation
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function afterSave($object)
    {
        $websiteId = $this->_storeManager->getStore($object->getStoreId())->getWebsiteId();
        $isGlobal = $this->getAttribute()->isScopeGlobal() || $websiteId == 0;

        $priceRows = $object->getData($this->getAttribute()->getName());
        if (null === $priceRows) {
            return $this;
        }

        $priceRows = array_filter((array)$priceRows);

        $old = [];
        $new = [];

        // prepare original data for compare
        $origPrices = $object->getOrigData($this->getAttribute()->getName());
        if (!is_array($origPrices)) {
            $origPrices = [];
        }
        foreach ($origPrices as $data) {
            if ($data['store_id'] > 0 || $data['store_id'] == '0' && $isGlobal) {
                $key = implode(
                    '-',
                    array_merge(
                        [$data['store_id'], $data['cust_group']],
                        $this->_getAdditionalUniqueFields($data)
                    )
                );
                $old[$key] = $data;
            }
        }

        // prepare data for save
        foreach ($priceRows as $data) {
            $hasEmptyData = false;
            foreach ($this->_getAdditionalUniqueFields($data) as $field) {
                if (empty($field)) {
                    $hasEmptyData = true;
                    break;
                }
            }

            if ($hasEmptyData || !isset($data['cust_group']) || !empty($data['delete'])) {
                continue;
            }
            if ($this->getAttribute()->isScopeGlobal() && $data['website_id'] > 0) {
                continue;
            }
            if (!$isGlobal && (int)$data['store_id'] == 0) {
                continue;
            }

            $key = implode(
                '-',
                array_merge([$data['store_id'], $data['cust_group']], $this->_getAdditionalUniqueFields($data))
            );

            $useForAllGroups = $data['cust_group'] == $this->_groupManagement->getAllCustomersGroup()->getId();
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;

            $new[$key] = array_merge(
                [
                    'store_id' => $data['store_id'],
                    'all_groups' => $useForAllGroups ? 1 : 0,
                    'customer_group_id' => $customerGroupId,
                    'value' => $data['price'],
                ],
                $this->_getAdditionalUniqueFields($data)
            );
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged = false;
        $productId = $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField());

        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->_getResource()->deletePriceData($productId, null, $data['price_id']);
                $isChanged = true;
            }
        }

        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new \Magento\Framework\DataObject($data);
                $price->setData(
                    $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField(),
                    $productId
                );

                $this->_getResource()->savePriceData($price);
                $isChanged = true;
            }
        }

        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value']) {
                    $price = new \Magento\Framework\DataObject([
                        'value_id' => $old[$k]['price_id'],
                        'value' => $v['value']
                    ]);

                    $this->_getResource()->savePriceData($price);
                    $isChanged = true;
                }
            }
        }

        $params = $this->request->getParams();
        if (!isset($params['product'][$this->getAttribute()->getName()])) {
            if ($this->helper->isAdmin() && $this->helper->checkRoute() == 'catalog_product') {
                if (isset($params['store'])) {
                    $storeId = $params['store'];
                    $this->_productAttributeBackendTierprice->deletePriceData($productId, $storeId);
                } else {
                    $this->_getResource()->deletePriceData($productId);
                }

                $isChanged = true;
            }
        }

        if ($isChanged) {
            $valueChangedKey = $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
        }

        return $this;
    }

    /**
     * Retrieve websites currency rates and base currency codes
     *
     * @return array
     */
    protected function _getWebsiteCurrencyRates()
    {
        if (is_null($this->_rates)) {
            $this->_rates = [];
            $baseCurrency = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );
            foreach ($this->_storeManager->getStores() as $store) {
                /* @var $website \Magento\Store\Model\Store */
                if ($store->getBaseCurrencyCode() != $baseCurrency) {
                    $rate = $this->_currencyFactory->create()->load(
                        $baseCurrency
                    )->getRate(
                        $store->getBaseCurrencyCode()
                    );
                    if (!$rate) {
                        $rate = 1;
                    }
                    $this->_rates[$store->getId()] = [
                        'code' => $store->getBaseCurrencyCode(),
                        'rate' => $rate,
                    ];
                } else {
                    $this->_rates[$store->getId()] = ['code' => $baseCurrency, 'rate' => 1];
                }
            }
        }
        return $this->_rates;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\EntityManager\MetadataPool');
        }
        return $this->metadataPool;
    }

    /**
     * Assign group prices to product data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $storeId = $object->getStoreId();
        $websiteId = null;
        if ($this->getAttribute()->isScopeGlobal()) {
            $websiteId = 0;
        } elseif ($storeId) {
            $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        }

        $data = $this->_getResource()->loadPriceData(
            $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()),
            $storeId
        );
        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = $this->_groupManagement->getAllCustomersGroup()->getId();
            }
        }

        if (!$object->getData('_edit_mode') && $websiteId) {
            $data = $this->preparePriceData($data, $object->getTypeId(), $websiteId);
        }

        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
    }


}
