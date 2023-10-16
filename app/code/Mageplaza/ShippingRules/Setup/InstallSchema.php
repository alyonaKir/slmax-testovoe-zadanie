<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingRules
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRules\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Mageplaza\ShippingRules\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('mageplaza_shippingrules_rule')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_shippingrules_rule'))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Rule Id')
                ->addColumn('name', Table::TYPE_TEXT, 255, [], 'Name')
                ->addColumn('description', Table::TYPE_TEXT, '64k', [], 'Rule Description')
                ->addColumn('status', Table::TYPE_SMALLINT, 1, [], 'Status')
                ->addColumn('store_ids', Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true,], 'Store Ids')
                ->addColumn('sale_rules_active', Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true,], 'Sale Rule Active')
                ->addColumn('sale_rules_inactive', Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true,], 'Sale Rule InActive')
                ->addColumn('schedule', Table::TYPE_TEXT, '2M', ['nullable' => false, 'unsigned' => true,], 'Schedule (Json)')
                ->addColumn('apply_fee', Table::TYPE_SMALLINT, 1, [], 'How to Apply Fee')
                ->addColumn('min_fee_change', Table::TYPE_DECIMAL, [12, 4], ['nullable' => true], 'Minimal Fee Change')
                ->addColumn('max_fee_change', Table::TYPE_DECIMAL, [12, 4], ['nullable' => true], 'Maximal Fee Change')
                ->addColumn('min_total_fee', Table::TYPE_DECIMAL, [12, 4], ['nullable' => true], 'Minimal Total Fee')
                ->addColumn('max_total_fee', Table::TYPE_DECIMAL, [12, 4], ['nullable' => true], 'Maximal Total Fee')
                ->addColumn('order_scope', Table::TYPE_TEXT, '2M', [], 'Order Scope (Json)')
                ->addColumn('cart_scope', Table::TYPE_TEXT, '2M', [], 'Cart Item Scope (Json)')
                ->addColumn('shipping_methods', Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true,], 'Shipping Methods')
                ->addColumn('customer_group', Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true], 'Customer Group')
                ->addColumn('started_at', Table::TYPE_TIMESTAMP, null, [], 'From Date')
                ->addColumn('finished_at', Table::TYPE_TIMESTAMP, null, [], 'To Date')
                ->addColumn('priority', Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Priority')
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Rule Conditions')
                ->addColumn('actions_serialized', Table::TYPE_TEXT, '2M', [], 'Actions Serialized')
                ->addColumn('apply_free_item', Table::TYPE_SMALLINT, 1, [], 'Apply For Free Shipping Items')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Rule Updated At')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Rule Created At')
                ->setComment('Shipping Rule Table');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
