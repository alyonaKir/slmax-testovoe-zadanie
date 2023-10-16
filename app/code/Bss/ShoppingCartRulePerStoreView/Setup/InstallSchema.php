<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ShoppingCartRulePerStoreView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

// @codingStandardsIgnoreFile

namespace Bss\ShoppingCartRulePerStoreView\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $setup->startSetup();

        $storesTable = $installer->getTable('store');
        /**
         * Create table 'salesrule_store' if not exists. This table will be used instead of
         * column website_ids of main catalog rules table
         */
        $table = $installer->getConnection()->newTable(
            $setup->getTable('salesrule_store')
        )->addColumn(
            'rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store Id'
        )->addIndex(
            $installer->getIdxName('salesrule_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('salesrule_store', 'rule_id', 'salesrule', 'rule_id'),
            'rule_id',
            $installer->getTable('salesrule'),
            'rule_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('salesrule_store', 'store_id', 'salesrule_store', 'store_id'),
            'store_id',
            $storesTable,
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Rules To Websites Relations'
        );

            $installer->getConnection()->createTable($table);

            $setup->endSetup();
    }
}
