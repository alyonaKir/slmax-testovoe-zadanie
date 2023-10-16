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

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\Redirects301Seo\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEOSUITE_REDIRECTS_URL = 'bss_redirects/redirects/redirects';
    const SEOSUITE_REDIRECTS_DAY = 'bss_redirects/redirects/delete_day';
    const SEOSUITE_REDIRECTS_ENABLE = 'bss_redirects/general/enable';

    /**
     * Get redirect Url
     *
     * @param string $storeId
     * @return mixed
     */
    public function getRedirectsUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_REDIRECTS_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Redirect day
     *
     * @param string $storeId
     * @return mixed
     */
    public function getRedirectsDay($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_REDIRECTS_DAY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get enable redirect
     *
     * @param string $storeId
     * @return mixed
     */
    public function getRedirectsEnable($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::SEOSUITE_REDIRECTS_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Gte url's suffix
     *
     * @param string $storeId
     * @return mixed
     */
    public function getProductSuffixUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/product_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get category's suffix
     *
     * @param string $storeId
     * @return mixed
     */
    public function getCategorySuffixUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
