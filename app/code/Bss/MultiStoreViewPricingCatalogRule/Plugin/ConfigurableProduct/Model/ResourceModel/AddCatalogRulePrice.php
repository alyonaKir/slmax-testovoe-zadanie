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
namespace Bss\MultiStoreViewPricingCatalogRule\Plugin\ConfigurableProduct\Model\ResourceModel;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;

class AddCatalogRulePrice
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data 
     */
    private $helper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->helper = $helper;
    }

    /**
     * @param Collection $productCollection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $productCollection, $printQuery = false, $logQuery = false)
    {
        if ($this->helper->isScopePrice()) {
            if (!$productCollection->hasFlag('catalog_rule_loaded')) {
                $connection = $this->resource->getConnection();
                $store = $this->storeManager->getStore();
                $productCollection->getSelect()
                    ->joinLeft(
                        ['catalog_rule' => $this->resource->getTableName('catalogrule_product_price_store')],
                        implode(' AND ', [
                            'catalog_rule.product_id = e.entity_id',
                            $connection->quoteInto('catalog_rule.store_id = ?', $store->getId()),
                            $connection->quoteInto(
                                'catalog_rule.customer_group_id = ?',
                                $this->customerSession->getCustomerGroupId()
                            ),
                            $connection->quoteInto(
                                'catalog_rule.rule_date = ?',
                                $this->dateTime->formatDate($this->localeDate->scopeDate($store->getId()), false)
                            ),
                        ]),
                        [CatalogRulePrice::PRICE_CODE => 'rule_price']
                    );
                $productCollection->setFlag('catalog_rule_loaded', true);
            }

            return [$printQuery, $logQuery];
        }
    }
}
