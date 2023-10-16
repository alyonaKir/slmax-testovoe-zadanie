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
namespace Bss\MultiStoreViewPricingTierPrice\Model\ResourceModel\Product\Attribute\Backend;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice;

class Tierprice extends AbstractGroupPrice
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        $connectionName = null
    ) {
        $this->helper = $helper;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity_tier_price_store', 'value_id');
    }

    /**
     * Add qty column
     *
     * @param array $columns
     * @return array
     */
    protected function _loadPriceDataColumns($columns)
    {
        $columns = parent::_loadPriceDataColumns($columns);
        $columns['price_qty'] = 'qty';
        return $columns;
    }

    /**
     * Order by qty
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _loadPriceDataSelect($select)
    {
        $select->order('qty');
        return $select;
    }

    /**
     * Load Tier Prices for product
     *
     * @param int $productId
     * @param int $websiteId
     * @return array
     */
    public function loadPriceData($productId, $storeId = null)
    {
        $connection = $this->getConnection();

        $columns = [
            'price_id' => $this->getIdFieldName(),
            'store_id' => 'store_id',
            'all_groups' => 'all_groups',
            'cust_group' => 'customer_group_id',
            'price' => 'value',
        ];

        $columns = $this->_loadPriceDataColumns($columns);

        $productIdFieldName = 'entity_id';
        $select = $connection->select()
            ->from($this->getMainTable(), $columns)
            ->where("{$productIdFieldName} = ?", $productId);

        $this->_loadPriceDataSelect($select);

        if ($storeId !== null) {
            if($storeId != 0)
                $select->where('store_id = ?', $storeId);
        }

        return $connection->fetchAll($select);
    }

    /**
     * Delete Tier Prices for product
     *
     * @param int $productId
     * @param int $websiteId
     * @param int $priceId
     * @return int The number of affected rows
     */
    public function deletePriceData($productId, $websiteId = null, $priceId = null)
    {
        $connection = $this->getConnection();

        $conds = [$connection->quoteInto('entity_id' . ' = ?', $productId)];

        if ($websiteId !== null) {
            $conds[] = $connection->quoteInto('store_id = ?', $websiteId);
        }

        if ($priceId !== null) {
            $conds[] = $connection->quoteInto($this->getIdFieldName() . ' = ?', $priceId);
        }

        $where = implode(' AND ', $conds);

        return $connection->delete($this->getMainTable(), $where);
    }

    /**
     * @param int|null $storeId
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSelect($storeId = null)
    {
        $columns = [
            'price_id' => $this->getIdFieldName(),
            'website_id' => 'store_id',
            'all_groups' => 'all_groups',
            'cust_group' => 'customer_group_id'
        ];

        $columns = $this->_loadPriceDataColumns($columns);

        $select = $this->getConnection()->select()
            ->from(['main_table' => $this->getMainTable()], $columns);

        if ($this->helper->getTierPriceConfig() == '0') {
            $select->joinRight(
                ['s' => $this->getTable('store')],
                'main_table.store_id = s.store_id',
                []
            );
            $select->join(
                ['dtp' => $this->getTable('catalog_product_entity_tier_price')],
                '(s.website_id = dtp.website_id OR dtp.website_id = 0) AND main_table.entity_id = dtp.entity_id AND main_table.all_groups = dtp.all_groups AND main_table.customer_group_id = dtp.customer_group_id AND main_table.qty = dtp.qty',
                []
            );

            $tierPriceValueExpr = $this->getConnection()->getCheckSql(
                'dtp.value',
                $this->getConnection()->getCheckSql(
                    'main_table.value',
                    $this->getConnection()->getCheckSql(
                        'dtp.value < main_table.value',
                        'dtp.value',
                        'main_table.value'
                    ),
                    'dtp.value'
                ),
                $this->getConnection()->getCheckSql(
                    'main_table.value',
                    'main_table.value',
                    0
                )
            );

            $select->columns(['price' => $tierPriceValueExpr]);
        } else {
            $select->columns(['price' => 'main_table.value']);
        }

        if ($storeId !== null) {
            if ($this->helper->getTierPriceConfig() == '0') {
                $select->where('s.store_id = ?', $storeId);
            } else {
                $select->where('store_id = ?', $storeId);
            }
        }

        return $select;
    }
}
