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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

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
        $tableName = $setup->getTable('bss_sitemap');

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $connection->dropColumn(
                    $tableName,
                    'sitemap_filename'
                );
                $connection->dropColumn(
                    $tableName,
                    'sitemap_path'
                );
                $connection->dropColumn(
                    $tableName,
                    'sitemap_time'
                );
                $connection->addColumn(
                    $tableName,
                    'xml_sitemap_filename',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'Google XML Sitemap Filename',
                    ]
                );
                $connection->addColumn(
                    $tableName,
                    'xml_sitemap_path',
                    ['type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'Google XML Sitemap Path',
                    ]
                );
                $connection->addColumn(
                    $tableName,
                    'xml_sitemap_time',
                    ['type' => Table::TYPE_TIMESTAMP,
                        'nullable' => true,
                        'comment' => 'Google XML Sitemap Time',
                    ]
                );
            }
        }
        if ($setup->getConnection()->isTableExists($tableName) == true && !$setup->getConnection()->tableColumnExists($tableName, 'entity_breakdown')) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $tableName,
                'entity_breakdown',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Google XML Sitemap Entity Breakdown',
                ]
            );
        }
        $setup->endSetup();
    }
}
