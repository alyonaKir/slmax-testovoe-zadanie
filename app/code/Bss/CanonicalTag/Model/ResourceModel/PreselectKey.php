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
 * @package    Bss_CanonicalTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CanonicalTag\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class PreselectKey
 *
 * @package Bss\CanonicalTag\Model\ResourceModel
 */
class PreselectKey extends AbstractDb
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init('bss_canonical_tag', 'product_id');
    }

    /**
     * @param string $url
     * @param string $productId
     * @param string $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveUrlValue($url, $productId, $storeId)
    {
        $connection = $this->getConnection();
        $bind = [
            'product_id' => $productId,
            'url_value' => $url,
            'store' => $storeId,
        ];
        $connection->insert($this->getMainTable(), $bind);
    }

    /**
     * @param string $productId
     * @param string $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteOldKey($productId, $storeId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['product_id=?' => $productId, 'store' => $storeId]);
    }
}
