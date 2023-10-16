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
namespace Bss\MultiStoreViewPricingPriceIndexer\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;

class DefaultPrice extends \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param string|null $connectionName
     * @param IndexTableStructureFactory|null $indexTableStructureFactory
     * @param array $priceModifiers
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        string $connectionName = null,
        IndexTableStructureFactory $indexTableStructureFactory = null,
        $priceModifiers = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $tableStrategy,
            $eavConfig,
            $eventManager,
            $moduleManager,
            $connectionName,
            $indexTableStructureFactory,
            $priceModifiers
        );
    }

    /**
     * Define main price index table
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->helper->isScopePrice()) {
            $this->_init('catalog_product_index_price_store', 'entity_id');
        } else {
            $this->_init('catalog_product_index_price', 'entity_id');
        }
    }

    /**
     * Retrieve final price temporary index table name
     *
     * @see _prepareDefaultFinalPriceTable()
     *
     * @return string
     */
    protected function _getDefaultFinalPriceTable()
    {
        if (!$this->helper->isScopePrice()) {
            return parent::_getDefaultFinalPriceTable();
        }

        return $this->tableStrategy->getTableName('catalog_product_index_price_final_store');
    }

    /**
     * Retrieve table name for product tier price index
     *
     * @return string
     */
    protected function _getTierPriceIndexTable()
    {
        if (!$this->helper->isScopePrice()) {
            return parent::_getTierPriceIndexTable();
        }

        return $this->getTable('catalog_product_index_tier_price_store');
    }

    /**
     * Forms Select for collecting price related data for final price index table
     * Next types of prices took into account: default, special, tier price
     * Moved to protected for possible reusing
     *
     * @param int|array $entityIds Ids for filtering output result
     * @param string|null $type Type for filtering output result by specified product type (all if null)
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 101.0.8
     */
    protected function getSelect($entityIds = null, $type = null)
    {
        if (!$this->helper->isScopePrice()) {
            return parent::getSelect($entityIds, $type);
        }

        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->join(
            ['cg' => $this->getTable('customer_group')],
            '',
            ['customer_group_id']
        )->join(
            ['cw' => $this->getTable('store_website')],
            '',
            ['website_id']
        )->join(
            ['cwd' => $this->_getWebsiteDateTable()],
            'cw.website_id = cwd.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'cs.store_id != 0',
            ['store_id']
        )->join(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id AND pw.website_id = cw.website_id',
            []
        )->joinLeft(
            ['tp' => $this->_getTierPriceIndexTable()],
            'tp.entity_id = e.entity_id AND tp.store_id = cs.store_id' .
            ' AND tp.customer_group_id = cg.customer_group_id',
            []
        );

        if ($type !== null) {
            $select->where('e.type_id = ?', $type);
        }

        // add enable products limitation
        $statusCond = $connection->quoteInto(
            '=?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        $this->_addAttributeToSelect(
            $select,
            'status',
            'e.' . $metadata->getLinkField(),
            'cs.store_id',
            $statusCond,
            true
        );
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->_addAttributeToSelect(
                $select,
                'tax_class_id',
                'e.' . $metadata->getLinkField(),
                'cs.store_id'
            );
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }
        $select->columns(['tax_class_id' => $taxClassId]);

        $price = $this->_addAttributeToSelect(
            $select,
            'price',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );
        $specialPrice = $this->_addAttributeToSelect(
            $select,
            'special_price',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );
        $specialFrom = $this->_addAttributeToSelect(
            $select,
            'special_from_date',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );
        $specialTo = $this->_addAttributeToSelect(
            $select,
            'special_to_date',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );
        $currentDate = 'cwd.website_date';

        $maxUnsignedBigint = '~0';
        $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialToDate = $connection->getDatePartSql($specialTo);
        $specialFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
        $specialToExpr = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
        $specialPriceExpr = $connection->getCheckSql(
            "{$specialPrice} IS NOT NULL AND {$specialFromExpr} AND {$specialToExpr}",
            $specialPrice,
            $maxUnsignedBigint
        );
        $tierPrice = new \Zend_Db_Expr('tp.min_price');
        $tierPriceExpr = $connection->getIfNullSql(
            $tierPrice,
            $maxUnsignedBigint
        );
        $finalPrice = $connection->getLeastSql([
            $price,
            $specialPriceExpr,
            $tierPriceExpr,
        ]);

        $select->columns(
            [
                'orig_price' => $connection->getIfNullSql($price, 0),
                'price' => $connection->getIfNullSql($finalPrice, 0),
                'min_price' => $connection->getIfNullSql($finalPrice, 0),
                'max_price' => $connection->getIfNullSql($finalPrice, 0),
                'tier_price' => $tierPrice,
                'base_tier' => $tierPrice,
            ]
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id'),
            ]
        );
        return $select;
    }

    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @param int[]|null $entityIds
     * @return $this
     */
    protected function _movePriceDataToIndexTable($entityIds = null)
    {
        if (!$this->helper->isScopePrice()) {
            return parent::_movePriceDataToIndexTable($entityIds);
        }

        $columns = [
            'entity_id' => 'entity_id',
            'customer_group_id' => 'customer_group_id',
            'website_id' => 'website_id',
            'store_id' => 'store_id',
            'tax_class_id' => 'tax_class_id',
            'price' => 'orig_price',
            'final_price' => 'price',
            'min_price' => 'min_price',
            'max_price' => 'max_price',
            'tier_price' => 'tier_price',
        ];

        $connection = $this->getConnection();
        $table = $this->_getDefaultFinalPriceTable();
        $select = $connection->select()->from($table, $columns);

        if ($entityIds !== null) {
            $select->where('entity_id in (?)', count($entityIds) > 0 ? $entityIds : 0);
        }

        $query = $select->insertFromSelect($this->getIdxTable(), [], false);
        $connection->query($query);

        $connection->delete($table);

        return $this;
    }

    /**
     * Retrieve temporary index table name
     *
     * @param string $table
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdxTable($table = null)
    {
        if (!$this->helper->isScopePrice()) {
            return parent::getIdxTable($table);
        }

        return $this->tableStrategy->getTableName('catalog_product_index_price_store');
    }
}
