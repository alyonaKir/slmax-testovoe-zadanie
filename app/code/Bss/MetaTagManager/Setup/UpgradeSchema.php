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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Bss\MetaTagManager\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('bss_product_meta_template');
        $connection->dropTable($connection->getTableName('bss_category_meta_template'));
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $connection->addColumn(
                $tableName,
                'conditions_serialized',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Conditions Serialized',
                ]
            );
            $connection->addColumn(
                $tableName,
                'meta_type',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Meta Tag Template Type',
                ]
            );
            $connection->addColumn(
                $tableName,
                'url_key',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Meta Tag Template URL Key',
                ]
            );
            $connection->addColumn(
                $tableName,
                'created_at',
                [
                    'type' => Table::TYPE_DATETIME,
                    'nullable' => false,
                    'comment' => 'Meta Tag Created At',
                ]
            );
            $connection->addColumn(
                $tableName,
                'updated_at',
                [
                    'type' => Table::TYPE_DATETIME,
                    'nullable' => false,
                    'comment' => 'Meta Tag Updated At',
                ]
            );
            $renameTableName = $setup->getTable('bss_meta_template');
            $connection->renameTable($tableName, $renameTableName);
        }
    }
}
