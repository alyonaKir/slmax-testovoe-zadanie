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
namespace Bss\MultiStoreViewPricingCatalogRule\Model\Indexer;

use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProduct;
use Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;
use Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder extends \Magento\CatalogRule\Model\Indexer\IndexBuilder
{
        /**
     * @var \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator
     */
    private $productPriceCalculator;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\ReindexRuleProduct
     */
    private $reindexRuleProduct;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite
     */
    private $reindexRuleGroupWebsite;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder
     */
    private $ruleProductsSelectBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice
     */
    private $reindexRuleProductPrice;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor
     */
    private $pricesPersistor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var TableSwapper
     */
    private $tableSwapper;

    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        $batchCount = 1000,
        \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator $productPriceCalculator = null,
        \Magento\CatalogRule\Model\Indexer\ReindexRuleProduct $reindexRuleProduct = null,
        \Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite $reindexRuleGroupWebsite = null,
        \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder $ruleProductsSelectBuilder = null,
        \Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice $reindexRuleProductPrice = null,
        \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor $pricesPersistor = null,
        \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher = null,
        TableSwapper $tableSwapper = null
    ) {
        parent::__construct(
            $ruleCollectionFactory,
            $priceCurrency,
            $resource,
            $storeManager,
            $logger,
            $eavConfig,
            $dateFormat,
            $dateTime,
            $productFactory,
            $batchCount,
            $productPriceCalculator,
            $reindexRuleProduct,
            $reindexRuleGroupWebsite,
            $ruleProductsSelectBuilder,
            $reindexRuleProductPrice,
            $pricesPersistor,
            $activeTableSwitcher
        );
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->logger = $logger;
        $this->priceCurrency = $priceCurrency;
        $this->eavConfig = $eavConfig;
        $this->dateFormat = $dateFormat;
        $this->dateTime = $dateTime;
        $this->productFactory = $productFactory;
        $this->batchCount = $batchCount;

        $this->productPriceCalculator = $productPriceCalculator ?? ObjectManager::getInstance()->get(
            ProductPriceCalculator::class
        );
        $this->reindexRuleProduct = $reindexRuleProduct ?? ObjectManager::getInstance()->get(
            ReindexRuleProduct::class
        );
        $this->reindexRuleGroupWebsite = $reindexRuleGroupWebsite ?? ObjectManager::getInstance()->get(
            ReindexRuleGroupWebsite::class
        );
        $this->ruleProductsSelectBuilder = $ruleProductsSelectBuilder ?? ObjectManager::getInstance()->get(
            RuleProductsSelectBuilder::class
        );
        $this->reindexRuleProductPrice = $reindexRuleProductPrice ?? ObjectManager::getInstance()->get(
            ReindexRuleProductPrice::class
        );
        $this->pricesPersistor = $pricesPersistor ?? ObjectManager::getInstance()->get(
            RuleProductPricesPersistor::class
        );
        $this->activeTableSwitcher = $activeTableSwitcher ?? ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class
        );
        $this->tableSwapper = $tableSwapper ??
            ObjectManager::getInstance()->get(TableSwapper::class);
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::doReindexFull();
        }

        $this->connection->truncateTable(
            $this->getTable($this->activeTableSwitcher->getAdditionalTableName('catalogrule_product'))
        );

        $this->connection->truncateTable(
            $this->getTable($this->activeTableSwitcher->getAdditionalTableName('catalogrule_product_price_store'))
        );

        foreach ($this->getAllRules() as $rule) {
            $this->reindexRuleProduct->execute($rule, $this->batchCount, true);
        }

        $this->reindexRuleProductPrice->execute($this->batchCount, null, true);
        $this->reindexRuleGroupWebsite->execute(true);

        $this->tableSwapper->swapIndexTables(
            [
                $this->getTable('catalogrule_product'),
                $this->getTable('catalogrule_product_price_store'),
                $this->getTable('catalogrule_group_website')
            ]
        );
    }

    /**
     * Clean by product ids
     *
     * @param array $productIds
     * @return void
     */
    protected function cleanByIds($productIds)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::cleanByIds($productIds);
        }

        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->resource->getTableName('catalogrule_product'), 'product_id')
                ->distinct()
                ->where('product_id IN (?)', $productIds),
            $this->resource->getTableName('catalogrule_product')
        );
        $this->connection->query($query);

        $query = $this->connection->deleteFromSelect(
            $this->connection->select()
                ->from($this->resource->getTableName('catalogrule_product_price_store'), 'product_id')
                ->distinct()
                ->where('product_id IN (?)', $productIds),
            $this->resource->getTableName('catalogrule_product_price_store')
        );
        $this->connection->query($query);
    }

    /**
     * Clean rule price index
     *
     * @return $this
     */
    protected function deleteOldData()
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::deleteOldData();
        }

        $this->connection->delete($this->getTable('catalogrule_product_price_store'));
        return $this;
    }
}
