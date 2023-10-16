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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\StoreResolverInterface;

class Configurable extends \Bss\MultiStoreViewPricingPriceIndexer\Model\ResourceModel\Product\Indexer\Base\Configurable
{
    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param null $connectionName
     * @param StoreResolverInterface|null $storeResolver
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        $connectionName = null,
        StoreResolverInterface $storeResolver = null
    ) {
        parent::__construct(
            $context,
            $tableStrategy,
            $eavConfig,
            $eventManager,
            $moduleManager,
            $helper,
            $connectionName
        );
        $this->storeResolver = $storeResolver ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(StoreResolverInterface::class);
    }


    /**
     * {@inheritdoc}
     */
    protected function reindex($entityIds = null)
    {
        if ($this->hasEntity() || !empty($entityIds)) {
            if (!empty($entityIds)) {
                $allEntityIds = $this->getRelatedProducts($entityIds);
                $this->prepareFinalPriceDataForType($allEntityIds, null);
            } else {
                $this->_prepareFinalPriceData($entityIds);
            }

            $this->_applyCustomOption();
            $this->_applyConfigurableOption($entityIds);
            $this->_movePriceDataToIndexTable($entityIds);
        }

        return $this;
    }

    /**
     * Get related product
     *
     * @param  int[] $entityIds
     * @return int[]
     */
    private function getRelatedProducts($entityIds)
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $select = $this->getConnection()->select()->union(
            [
                $this->getConnection()->select()
                    ->from(
                        ['e' => $this->getTable('catalog_product_entity')],
                        'e.entity_id'
                    )->join(
                        ['cpsl' => $this->getTable('catalog_product_super_link')],
                        'cpsl.parent_id = e.' . $metadata->getLinkField(),
                        []
                    )->where(
                        'e.entity_id IN (?)',
                        $entityIds
                    ),
                $this->getConnection()->select()
                    ->from(
                        ['cpsl' => $this->getTable('catalog_product_super_link')],
                        'cpsl.product_id'
                    )->join(
                        ['e' => $this->getTable('catalog_product_entity')],
                        'cpsl.parent_id = e.' . $metadata->getLinkField(),
                        []
                    )->where(
                        'e.entity_id IN (?)',
                        $entityIds
                    ),
                $this->getConnection()->select()
                    ->from($this->getTable('catalog_product_super_link'), 'product_id')
                    ->where('product_id in (?)', $entityIds),
            ]
        );

        return array_map('intval', $this->getConnection()->fetchCol($select));
    }

    /**
     * Retrieve table name for custom option temporary aggregation data
     *
     * @return string
     */
    protected function _getConfigurableOptionAggregateTable()
    {
        if (!$this->helper->isScopePrice()) {
            return parent::_getConfigurableOptionAggregateTable();
        }

        return $this->tableStrategy->getTableName('catalog_product_index_price_cfg_opt_agr_store');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     */
    protected function _getConfigurableOptionPriceTable()
    {
        if (!$this->helper->isScopePrice()) {
            return parent::_getConfigurableOptionPriceTable();
        }

        return $this->tableStrategy->getTableName('catalog_product_index_price_cfg_opt_store');
    }

    /**
     * {@inheritdoc}
     */
    protected function _applyConfigurableOption($entityIds = null)
    {
        if (!$this->helper->isScopePrice()) {
            return parent::_applyConfigurableOption();
        }

        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $connection = $this->getConnection();
        $copTable = $this->_getConfigurableOptionPriceTable();
        $finalPriceTable = $this->_getDefaultFinalPriceTable();
        $linkField = $metadata->getLinkField();

        $this->_prepareConfigurableOptionPriceTable();

        $select = $connection->select()->from(
            ['i' => $this->getIdxTable()],
            []
        )->join(
            ['l' => $this->getTable('catalog_product_super_link')],
            'l.product_id = i.entity_id',
            []
        )->join(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.' . $linkField . ' = l.parent_id',
            []
        )->columns(
            [
                'le.entity_id',
                'customer_group_id',
                'website_id',
                'store_id',
                'MIN(final_price)',
                'MAX(final_price)',
                'MIN(tier_price)',

            ]
        )->group(
            ['le.entity_id', 'customer_group_id', 'store_id']
        );
        if ($entityIds !== null) {
            $select->where('le.entity_id IN (?)', $entityIds);
        }

        $query = $select->insertFromSelect($copTable);
        $connection->query($query);

        $table = ['i' => $finalPriceTable];
        $select = $connection->select()->join(
            ['io' => $copTable],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.store_id = io.store_id',
            []
        );

        // adds price of custom option, that was applied in DefaultPrice::_applyCustomOption
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price - i.orig_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price - i.orig_price + io.max_price'),
                'tier_price' => 'io.tier_price',
            ]
        );

        $query = $select->crossUpdateFromSelect($table);
        $connection->query($query);

        $connection->delete($copTable);

        return $this;
    }
}
