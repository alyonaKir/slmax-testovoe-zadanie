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
namespace Bss\Redirects301Seo\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SaveUrlKeyDeleted
 *
 * @package Bss\Redirects301Seo\Model\ResourceModel
 */
class SaveUrlKeyDeleted extends AbstractDb
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init('bss_redirects', 'product_id');
    }

    /**
     * @param string $url
     * @param string $productId
     * @param string $date
     * @param string $categoriesId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveUrlValue($url, $productId, $date, $categoriesId)
    {
        $connection = $this->getConnection();
        $bind = [
            'product_id' => $productId,
            'url_deleted' => $url,
            'update_at' => $date,
            'categories_id' => $categoriesId
        ];
        $connection->insert($this->getMainTable(), $bind);
    }

    /**
     * @param string $productId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteUrlValue($productId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['product_id=?' => $productId]);
    }
}
