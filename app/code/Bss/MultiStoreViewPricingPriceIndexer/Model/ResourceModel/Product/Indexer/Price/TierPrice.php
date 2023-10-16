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
declare(strict_types=1);

namespace Bss\MultiStoreViewPricingPriceIndexer\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice as TierPriceResourceModel;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class TierPrice extends AbstractDb
{
    /**
     * @var TierPriceResourceModel
     */
    private $tierPriceResourceModel;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param TierPriceResourceModel $tierPriceResourceModel
     * @param MetadataPool $metadataPool
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        TierPriceResourceModel $tierPriceResourceModel,
        MetadataPool $metadataPool,
        ProductAttributeRepositoryInterface $attributeRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        string $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        $this->tierPriceResourceModel = $tierPriceResourceModel;
        $this->metadataPool = $metadataPool;
        $this->attributeRepository = $attributeRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_tier_price_store', 'entity_id');
    }

    /**
     * Retrieve final price temporary index table name
     *
     * @return string
     */
    private function getTemporaryTierPriceTable()
    {
        return $this->getTableName('catalog_product_index_tier_price_store_temp');
    }

    /**
     * Retrieve tier price index table template name
     *
     * @return string
     */
    private function getTemporaryTierPriceTmpTable()
    {
        return $this->getTableName('catalog_product_index_tier_price_store_tmp');
    }

    /**
     * Reindex tier price for entities.
     *
     * @param array $entityIds
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexEntity(array $entityIds = [])
    {
        $this->getConnection()->delete($this->getMainTable(), ['entity_id IN (?)' => $entityIds]);

        //separate by variations for increase performance
        $tierPriceVariations = [
            [true, true], //all websites; all customer groups
            [true, false], //all websites; specific customer group
            [false, true], //specific website; all customer groups
            [false, false], //specific website; specific customer group
        ];

        $this->prepareTemporaryTierPriceTable();
        foreach ($tierPriceVariations as $variation) {
            list ($isAllWebsites, $isAllCustomerGroups) = $variation;

            // Collect default tier price values
            $select = $this->getDefaultTierPriceSelect($isAllWebsites, $isAllCustomerGroups, $entityIds);
            $query = $select->insertFromSelect($this->getTemporaryTierPriceTable());
            $this->getConnection()->query($query);

            // Collect store tier price values
            $storeSelect = $this->getStoreTierPriceSelect($isAllWebsites, $isAllCustomerGroups, $entityIds);
            $storeQuery = $storeSelect->insertFromSelect($this->getTemporaryTierPriceTable());
            $this->getConnection()->query($storeQuery);
        }

        $finalSelect = $this->getFinalSelect();
        $finalQuery = $finalSelect->insertFromSelect($this->getMainTable());
        $this->getConnection()->query($finalQuery);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareTemporaryTierPriceTable()
    {
        $this->getConnection('indexer')->createTemporaryTableLike(
            $this->getTableName($this->getTemporaryTierPriceTable()),
            $this->getTableName($this->getTemporaryTierPriceTmpTable()),
            true
        );
        return $this->getTemporaryTierPriceTable();
    }

    /**
     * Join websites - stores table.
     * If $isAllWebsites is true, for each website will be used default value for all websites,
     * otherwise per each website will be used their own values.
     *
     * @param Select $select
     * @param bool $isAllWebsites
     */
    private function joinWebsitesStores(Select $select, bool $isAllWebsites)
    {
        $websiteTable = ['website' => $this->getTable('store_website')];
        if ($isAllWebsites) {
            $select->joinCross($websiteTable, [])
                ->where('website.website_id > ?', 0)
                ->where('tier_price.website_id = ?', 0);
        } else {
            $select->join($websiteTable, 'website.website_id = tier_price.website_id', [])
                ->where('tier_price.website_id > 0');
        }

        $storeTable = ['store' => $this->getTable('store')];
        $select->join(
            $storeTable,
            'store.website_id = website.website_id',
            []
        );
    }

    /**
     * Join stores table.
     * If $isAllStores is true, for each store will be used default value for all store,
     * otherwise per each store will be used their own values.
     *
     * @param Select $select
     * @param bool $isAllStores
     */
    private function joinStores(Select $select, bool $isAllStores)
    {
        $storeTable = ['store' => $this->getTable('store')];
        if ($isAllStores) {
            $select->joinCross($storeTable, [])
                ->where('store.store_id > ?', 0)
                ->where('tier_price.store_id = ?', 0);
        } else {
            $select->join($storeTable, 'store.store_id = tier_price.store_id', [])
                ->where('tier_price.store_id > 0');
        }
    }

    /**
     * Join customer groups table.
     * If $isAllCustomerGroups is true, for each customer group will be used default value for all customer groups,
     * otherwise per each customer group will be used their own values.
     *
     * @param Select $select
     * @param bool $isAllCustomerGroups
     */
    private function joinCustomerGroups(Select $select, bool $isAllCustomerGroups)
    {
        $customerGroupTable = ['customer_group' => $this->getTable('customer_group')];
        if ($isAllCustomerGroups) {
            $select->joinCross($customerGroupTable, [])
                ->where('tier_price.all_groups = ?', 1)
                ->where('tier_price.customer_group_id = ?', 0);
        } else {
            $select->join($customerGroupTable, 'customer_group.customer_group_id = tier_price.customer_group_id', [])
                ->where('tier_price.all_groups = ?', 0);
        }
    }

    /**
     * Join price table and return price value.
     *
     * @param Select $select
     * @param string $linkField
     * @return string
     */
    private function joinPrice(Select $select, string $linkField): string
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $priceAttribute */
        $priceAttribute = $this->attributeRepository->get('price');
        $select->joinLeft(
            ['entity_price_default' => $priceAttribute->getBackend()->getTable()],
            'entity_price_default.' . $linkField . ' = entity.' . $linkField
            . ' AND entity_price_default.attribute_id = ' . $priceAttribute->getAttributeId()
            . ' AND entity_price_default.store_id = 0',
            []
        );
        $priceValue = 'entity_price_default.value';

        if (!$priceAttribute->isScopeGlobal()) {
            $select->joinLeft(
                ['store_group' => $this->getTable('store_group')],
                'store_group.group_id = website.default_group_id',
                []
            )->joinLeft(
                ['entity_price_store' => $priceAttribute->getBackend()->getTable()],
                'entity_price_store.' . $linkField . ' = entity.' . $linkField
                . ' AND entity_price_store.attribute_id = ' . $priceAttribute->getAttributeId()
                . ' AND entity_price_store.store_id = store_group.default_store_id',
                []
            );
            $priceValue = $this->getConnection()
                ->getIfNullSql('entity_price_store.value', 'entity_price_default.value');
        }

        return (string) $priceValue;
    }

    /**
     * Build select for getting tier price data.
     *
     * @param bool $isAllWebsites
     * @param bool $isAllCustomerGroups
     * @param array $entityIds
     * @return Select
     */
    private function getDefaultTierPriceSelect(bool $isAllWebsites, bool $isAllCustomerGroups, array $entityIds = []): Select
    {
        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select();
        $select->from(['tier_price' => $this->tierPriceResourceModel->getMainTable()], [])
            ->where('tier_price.qty = ?', 1);

        $select->join(
            ['entity' => $this->getTable('catalog_product_entity')],
            "entity.{$linkField} = tier_price.{$linkField}",
            []
        );
        if (!empty($entityIds)) {
            $select->where('entity.entity_id IN (?)', $entityIds);
        }
        $this->joinWebsitesStores($select, $isAllWebsites);
        $this->joinCustomerGroups($select, $isAllCustomerGroups);

        $useOnlyStoreValueStores = $this->getUseOnlyStoreValueStores();
        if (!empty($useOnlyStoreValueStores)) {
            $select->where('store.store_id NOT IN (?)', $useOnlyStoreValueStores);
        }

        $priceValue = $this->joinPrice($select, $linkField);
        $tierPriceValue = 'tier_price.value';
        $tierPricePercentageValue = 'tier_price.percentage_value';
        $tierPriceValueExpr = $this->getConnection()->getCheckSql(
            $tierPriceValue,
            $tierPriceValue,
            sprintf('(1 - %s / 100) * %s', $tierPricePercentageValue, $priceValue)
        );
        $select->columns(
            [
                'entity.entity_id',
                'customer_group.customer_group_id',
                'store.store_id',
                'tier_price' => $tierPriceValueExpr,
            ]
        );

        return $select;
    }

    /**
     * Build select for getting tier price store data.
     *
     * @param bool $isAllWebsites
     * @param bool $isAllCustomerGroups
     * @param array $entityIds
     * @return Select
     */
    private function getStoreTierPriceSelect(bool $isAllWebsites, bool $isAllCustomerGroups, array $entityIds = []): Select
    {
        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select();
        $select->from(['tier_price' => $this->getTableName('catalog_product_entity_tier_price_store')], [])
            ->where('tier_price.qty = ?', 1);

        $select->join(
            ['entity' => $this->getTable('catalog_product_entity')],
            "entity.{$linkField} = tier_price.{$linkField}",
            []
        );
        if (!empty($entityIds)) {
            $select->where('entity.entity_id IN (?)', $entityIds);
        }
        $this->joinStores($select, $isAllWebsites);
        $this->joinCustomerGroups($select, $isAllCustomerGroups);

        $tierPriceValue = 'tier_price.value';
        $select->columns(
            [
                'entity.entity_id',
                'customer_group.customer_group_id',
                'store.store_id',
                'tier_price' => $tierPriceValue,
            ]
        );

        return $select;
    }

    /**
     * Get final Select for tier price store index.
     *
     * @return Select
     */
    private function getFinalSelect()
    {
        $select = $this->getConnection()->select();
        $select->from(['main_table' => $this->getTemporaryTierPriceTable()], []);
        $select->group(['entity_id', 'customer_group_id', 'store_id']);
        $select->columns(
            [
                'entity_id',
                'customer_group_id',
                'store_id',
                'tier_price' => 'MIN(min_price)',
            ]
        );

        return $select;
    }

    /**
     * Get real table name for db table, validated by db adapter
     *
     * @param string $tableName
     * @return string
     * @api
     */
    private function getTableName($tableName)
    {
        if (is_array($tableName)) {
            $cacheName = join('@', $tableName);
            list($tableName, $entitySuffix) = $tableName;
        } else {
            $cacheName = $tableName;
            $entitySuffix = null;
        }

        if ($entitySuffix !== null) {
            $tableName .= '_' . $entitySuffix;
        }

        if (!isset($this->_tables[$cacheName])) {
            $connectionName = $this->connectionName;
            $this->_tables[$cacheName] = $this->_resources->getTableName($tableName, $connectionName);
        }
        return $this->_tables[$cacheName];
    }

    /**
     * @return array
     */
    private function getUseOnlyStoreValueStores()
    {
        $allStores = $this->storeManager->getStores(false, true);
        $exclStores = [];

        foreach ($allStores as $code => $store) {
            $isUseOnlyStore = $this->scopeConfig->isSetFlag(
                'multistoreviewpricing/general/tier_price',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $code
            );

            if ($isUseOnlyStore) {
                $exclStores[] = $store->getId();
            }
        }

        return $exclStores;
    }
}
