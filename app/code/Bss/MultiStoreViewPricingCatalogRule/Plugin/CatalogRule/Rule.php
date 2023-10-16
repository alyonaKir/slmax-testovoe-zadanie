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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category  BSS
 * @package   Bss_MultiStoreViewPricing
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Plugin\CatalogRule;

class Rule
{
    public $helper;
    public $storeManager;

    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    public function aroundGetRulePrices(\Magento\CatalogRule\Model\ResourceModel\Rule $subject, \Closure $proceed, \DateTimeInterface $date, $websiteId, $customerGroupId, $productIds)
    {
        if (!$this->helper->isScopePrice()) {
            $result = $proceed($date, $websiteId, $customerGroupId, $productIds);
            return $result;
        }

        $currentStoreId = $this->storeManager->getStore()->getId();

        $connection = $subject->getConnection();
        $select = $connection->select()
            ->from($subject->getTable('catalogrule_product_price_store'), ['product_id', 'rule_price'])
            ->where('rule_date = ?', $date->format('Y-m-d'))
            ->where('store_id = ?', $currentStoreId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id IN(?)', $productIds);

        return $connection->fetchPairs($select);
    }
}
