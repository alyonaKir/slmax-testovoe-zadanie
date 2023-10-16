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
namespace Bss\MultiStoreViewPricingPriceIndexer\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $setup->startSetup();
 
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            /**
             * Create table 'catalog_product_index_price_bundle_store_idx'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalog_product_index_price_bundle_store_idx'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group Id'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Id'
                )
                ->addColumn(
                    'tax_class_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'default' => '0'],
                    'Tax Class Id'
                )
                ->addColumn(
                    'price_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Price Type'
                )
                ->addColumn(
                    'special_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Special Price'
                )
                ->addColumn(
                    'tier_percent',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Percent'
                )
                ->addColumn(
                    'orig_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Orig Price'
                )
                ->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Price'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Min Price'
                )
                ->addColumn(
                    'max_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Max Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->addColumn(
                    'base_tier',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Base Tier'
                )
                ->setComment('Catalog Product Index Price Bundle Store Idx');

            $installer->getConnection()->createTable($table);

            /**
             * Create table 'catalog_product_index_price_bundle_store_tmp'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalog_product_index_price_bundle_store_tmp'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group Id'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Id'
                )
                ->addColumn(
                    'tax_class_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'default' => '0'],
                    'Tax Class Id'
                )
                ->addColumn(
                    'price_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Price Type'
                )
                ->addColumn(
                    'special_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Special Price'
                )
                ->addColumn(
                    'tier_percent',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Percent'
                )
                ->addColumn(
                    'orig_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Orig Price'
                )
                ->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Price'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Min Price'
                )
                ->addColumn(
                    'max_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Max Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->addColumn(
                    'base_tier',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Base Tier'
                )
                ->setOption(
                    'type',
                    \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                )
                ->setComment('Catalog Product Index Price Bundle Tmp');

            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            /**
             * Create table 'catalog_product_index_price_bundle_sel_store_idx'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalog_product_index_price_bundle_sel_store_idx'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group Id'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Id'
                )
                ->addColumn(
                    'option_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                    'Option Id'
                )
                ->addColumn(
                    'selection_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                    'Selection Id'
                )
                ->addColumn(
                    'group_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'default' => '0'],
                    'Group Type'
                )
                ->addColumn(
                    'is_required',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'default' => '0'],
                    'Is Required'
                )
                ->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->setComment('Catalog Product Index Price Bundle Sel Idx');

            $installer->getConnection()->createTable($table);

            /**
             * Create table 'catalog_product_index_price_bundle_sel_store_tmp'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalog_product_index_price_bundle_sel_store_tmp'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group Id'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Id'
                )
                ->addColumn(
                    'option_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                    'Option Id'
                )
                ->addColumn(
                    'selection_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                    'Selection Id'
                )
                ->addColumn(
                    'group_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'default' => '0'],
                    'Group Type'
                )
                ->addColumn(
                    'is_required',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'default' => '0'],
                    'Is Required'
                )
                ->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->setOption(
                    'type',
                    \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                )
                ->setComment('Catalog Product Index Price Bundle Sel Tmp');

            $installer->getConnection()->createTable($table);

            /**
             * Create table 'catalog_product_index_price_bundle_opt_store_idx'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalog_product_index_price_bundle_opt_store_idx'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group Id'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Id'
                )
                ->addColumn(
                    'option_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                    'Option Id'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Min Price'
                )
                ->addColumn(
                    'alt_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Alt Price'
                )
                ->addColumn(
                    'max_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Max Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->addColumn(
                    'alt_tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Alt Tier Price'
                )
                ->setComment('Catalog Product Index Price Bundle Opt Idx');

            $installer->getConnection()->createTable($table);

            /**
             * Create table 'catalog_product_index_price_bundle_opt_store_tmp'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalog_product_index_price_bundle_opt_store_tmp'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group Id'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Id'
                )
                ->addColumn(
                    'option_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                    'Option Id'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Min Price'
                )
                ->addColumn(
                    'alt_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Alt Price'
                )
                ->addColumn(
                    'max_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Max Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->addColumn(
                    'alt_tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Alt Tier Price'
                )
                ->setOption(
                    'type',
                    \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                )
                ->setComment('Catalog Product Index Price Bundle Opt Tmp');

            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            /**
             * Create table 'catalog_product_index_price_cfg_opt_agr_store_idx'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('catalog_product_index_price_cfg_opt_agr_store_idx')
                )
                ->addColumn(
                    'parent_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Parent ID'
                )
                ->addColumn(
                    'child_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Child ID'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                )
                ->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->setComment(
                    'Catalog Product Price Indexer Config Option Aggregate Store Index Table'
                );
            $installer->getConnection()
                ->createTable($table);

            /**
             * Create table 'catalog_product_index_price_cfg_opt_agr_store_tmp'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('catalog_product_index_price_cfg_opt_agr_store_tmp')
                )
                ->addColumn(
                    'parent_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Parent ID'
                )
                ->addColumn(
                    'child_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Child ID'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                )
                ->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->setOption(
                    'type',
                    \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                )
                ->setComment(
                    'Catalog Product Price Indexer Config Option Aggregate Temp Store Table'
                );
            $installer->getConnection()
                ->createTable($table);

            /**
             * Create table 'catalog_product_index_price_cfg_opt_store_idx'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('catalog_product_index_price_cfg_opt_store_idx')
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Min Price'
                )
                ->addColumn(
                    'max_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Max Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->setComment(
                    'Catalog Product Price Indexer Config Option Index Table'
                );
            $installer->getConnection()
                ->createTable($table);

            /**
             * Create table 'catalog_product_index_price_cfg_opt_store_tmp'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('catalog_product_index_price_cfg_opt_store_tmp')
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Min Price'
                )
                ->addColumn(
                    'max_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Max Price'
                )
                ->addColumn(
                    'tier_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tier Price'
                )
                ->setOption(
                    'type',
                    \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                )
                ->setComment(
                    'Catalog Product Price Indexer Config Option Temp Store Table'
                );
            $installer->getConnection()
                ->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            /**
             * Create table 'catalog_product_index_price_downlod_store_idx'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalog_product_index_price_downlod_store_idx'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Minimum price'
                )
                ->addColumn(
                    'max_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Maximum price'
                )
                ->setComment('Indexer Table for price of downloadable products store view');
            $installer->getConnection()->createTable($table);

            /**
             * Create table 'catalog_product_index_price_downlod_store_tmp'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalog_product_index_price_downlod_store_tmp'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Website ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Minimum price'
                )
                ->addColumn(
                    'max_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Maximum price'
                )
                ->setOption(
                    'type',
                    \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
                )
                ->setComment('Temporary Indexer Table for price of downloadable products store view');
            $installer->getConnection()->createTable($table);

            $this->addReplicaTable(
                $setup,
                'catalog_product_index_price_store',
                'catalog_product_index_price_store_replica'
            );
        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $customerGroupTable = $setup->getConnection()->describeTable($setup->getTable('customer_group'));
            $customerGroupIdType = $customerGroupTable['customer_group_id']['DATA_TYPE'] == 'int'
                ? \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER : $customerGroupTable['customer_group_id']['DATA_TYPE'];

            /**
             * Create table 'catalog_product_index_tier_price_store'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('catalog_product_index_tier_price_store')
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'customer_group_id',
                    $customerGroupIdType,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Min Price'
                )
                ->addIndex(
                    $installer->getIdxName('catalog_product_index_tier_price_store', ['customer_group_id']),
                    ['customer_group_id']
                )
                ->addIndex(
                    $installer->getIdxName('catalog_product_index_tier_price_store', ['store_id']),
                    ['store_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'catalog_product_index_tier_price_store',
                        'customer_group_id',
                        'customer_group',
                        'customer_group_id'
                    ),
                    'customer_group_id',
                    $installer->getTable('customer_group'),
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'catalog_product_index_tier_price_store',
                        'entity_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'entity_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('catalog_product_index_tier_price_store', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment(
                    'Catalog Product Tier Price Store Index Table'
                );
            $installer->getConnection()->createTable($table);

            /**
             * Create table 'catalog_product_index_tier_price_store'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('catalog_product_index_tier_price_store_tmp')
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => false],
                    'Entity ID'
                )
                ->addColumn(
                    'customer_group_id',
                    $customerGroupIdType,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => false],
                    'Customer Group ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => false],
                    'Store ID'
                )
                ->addColumn(
                    'min_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Min Price'
                )
                ->addIndex(
                    $installer->getIdxName('catalog_product_index_tier_price_store_tmp', ['customer_group_id']),
                    ['customer_group_id']
                )
                ->addIndex(
                    $installer->getIdxName('catalog_product_index_tier_price_store_tmp', ['store_id']),
                    ['store_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'catalog_product_index_tier_price_store_tmp',
                        'customer_group_id',
                        'customer_group',
                        'customer_group_id'
                    ),
                    'customer_group_id',
                    $installer->getTable('customer_group'),
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'catalog_product_index_tier_price_store_tmp',
                        'entity_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'entity_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('catalog_product_index_tier_price_store_tmp', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment(
                    'Catalog Product Tier Price Store Index Table Template'
                );
            $installer->getConnection()->createTable($table);
        }
 
        $setup->endSetup();
    }

    /**
     * Add the replica table for existing one.
     *
     * @param SchemaSetupInterface $setup
     * @param string $existingTable
     * @param string $replicaTable
     * @return void
     */
    private function addReplicaTable(SchemaSetupInterface $setup, $existingTable, $replicaTable)
    {
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS %s LIKE %s',
            $setup->getConnection()->quoteIdentifier($setup->getTable($replicaTable)),
            $setup->getConnection()->quoteIdentifier($setup->getTable($existingTable))
        );
        $setup->getConnection()->query($sql);
    }
}
