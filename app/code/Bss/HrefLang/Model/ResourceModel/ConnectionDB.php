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
 * @package    Bss_HrefLang
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\HrefLang\Model\ResourceModel;

/**
 * Class ConnectionDB
 *
 * @package Bss\HrefLang\Model\ResourceModel
 */
class ConnectionDB
{
    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * ConnectionDB constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
        $this->writeAdapter = $this->resourceConnection->getConnection('core_write');
    }

    /**
     * Get cms page
     *
     * @param int $storeId
     * @param bool $cmsId
     * @param bool $cmsIdent
     * @return array
     */
    public function getCms($storeId, $cmsId, $cmsIdent)
    {
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('url_rewrite')],
                ['main_table.request_path']
            )
            ->where('main_table.entity_type = :entity_type');
        if ($storeId) {
            $select->where('main_table.store_id = :store_id');
            $bind[':store_id'] = $storeId;
        }
        if ($cmsId) {
            $select->where('main_table.entity_id = :entity_id');
            $bind[':entity_id'] = $cmsId;
        }
        if ($cmsIdent) {
            $select->where('main_table.request_path = :request_path');
            $select->joinLeft(
                ['cms_page_table' => $this->getTableName('cms_page')],
                'main_table.entity_id = cms_page_table.page_id',
                ['cms_page_table.is_active']
            );
            $bind[':request_path'] = $cmsIdent;
        }
        $bind[':entity_type'] = 'cms-page';
        $result = $this->readAdapter->fetchRow($select, $bind);
        return $result;
    }

    /**
     * Get table name
     *
     * @param string $entity
     * @return bool|string
     */
    public function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }
}
