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
 * @package    Bss_Breadcrumbs
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Breadcrumbs\Model\ResourceModel;

/**
 * Class Path
 *
 * @package Bss\Breadcrumbs\Model\ResourceModel
 */
class Path extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_entity', 'entity_id');
    }

    /**
     * @param string $priorityId
     * @param string $entityId
     */
    public function update($priorityId, $entityId)
    {
        $connection = $this->getConnection();
        $where = ['entity_id IN (?)' => $entityId];
        $connection->update($this->getTable('catalog_category_entity'), ['priority_id' => $priorityId], $where);
    }
}
