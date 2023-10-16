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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SeoReport
 * @package Bss\SeoReport\Model\ResourceModel
 */
class SeoReport extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_seo_report', 'url_rewrite_id');
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($data)
    {
        $connection = $this->getConnection();
        $connection->insert($this->getTable('bss_seo_report'), $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteData($reportId)
    {
        $this->getConnection()->delete($this->getTable('bss_seo_report'), ['url_rewrite_id=?' => $reportId]);
    }

    /**
     * @inheritdoc
     */
    public function updateData($dataUpdate, $entityId)
    {
        $connection = $this->getConnection();
        $where = ['url_rewrite_id=?' => $entityId];
        $connection->update($this->getTable('bss_seo_report'), $dataUpdate, $where);
    }
}
