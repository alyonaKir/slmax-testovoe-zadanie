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
 * @package    Bss_Redirects301Seo
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Redirects301Seo\Setup;

/**
 * Class InstallSchema
 *
 * @package Bss\Redirects301Seo\Setup
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * Install schema
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('bss_redirects')) {
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('bss_redirects')
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'auto_increment' => true, 'nullable' => false],
                    'Key ID'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false],
                    'Product ID'
                )->addColumn(
                    'url_deleted',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'URL Value'
                )->addColumn(
                    'categories_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'URL Value'
                )->addColumn(
                    'update_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    10,
                    ['nullable' => false],
                    'Time and Date'
                )->addIndex(
                    $installer->getIdxName('bss_redirects', ['product_id']),
                    ['product_id']
                )
                ->setComment(
                    'Pre select key for configurable product'
                );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
