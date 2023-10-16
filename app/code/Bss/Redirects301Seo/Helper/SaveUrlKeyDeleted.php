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
namespace Bss\Redirects301Seo\Helper;

/**
 * Class SaveUrlKeyDeleted
 *
 * @package Bss\Redirects301Seo\Helper
 */
class SaveUrlKeyDeleted
{
    /**
     * @var \Bss\Redirects301Seo\Model\ResourceModel\SaveUrlKeyDeleted
     */
    private $saveUrl;

    /**
     * SaveUrlKeyDeleted constructor.
     * @param \Bss\Redirects301Seo\Model\ResourceModel\SaveUrlKeyDeleted $saveUrl
     */
    public function __construct(
        \Bss\Redirects301Seo\Model\ResourceModel\SaveUrlKeyDeleted $saveUrl
    ) {
        $this->saveUrl = $saveUrl;
    }

    /**
     * @param string $postData
     * @param string $productId
     * @param string $date
     * @param string $categoriesId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveUrlValue($postData, $productId, $date, $categoriesId)
    {
        $this->saveUrl->saveUrlValue($postData, $productId, $date, $categoriesId);
    }

    /**
     * Delete product url
     *
     * @param int $productId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteUrlValue($productId)
    {
        $this->saveUrl->deleteUrlValue($productId);
    }
}
