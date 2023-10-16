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
namespace Bss\CanonicalTag\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\CanonicalTag\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEOSUITE_CANONICAL_ENABLE = 'bss_canonical/general/enable';
    const SEOSUITE_STOREVIEW = 'bss_canonical/homepage/store_view';
    const SEOSUITE_URL_HOMEPAGE = 'bss_canonical/homepage/url_homepage';
    const SEOSUITE_PRODUCT_PATH = 'bss_canonical/product/path';
    const SEOSUITE_CANONICAL_CATEGORY = 'bss_canonical/category/use_next_tag';
    const SEOSUITE_CANONICAL_LAYERED = 'bss_canonical/category/use_layered_navigation';

    /**
     * Get storeview
     *
     * @param string $storeId
     * @return mixed
     */
    public function getStoreView($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_STOREVIEW,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get canonical category
     *
     * @param string $storeId
     * @return mixed
     */
    public function isCanonicalCategory($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::SEOSUITE_CANONICAL_CATEGORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Url homepage
     *
     * @param string $storeId
     * @return mixed
     */
    public function getUrlHomepage($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_URL_HOMEPAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get enable module
     *
     * @param string $storeId
     * @return mixed
     */
    public function isCanonicalEnable($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::SEOSUITE_CANONICAL_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnableLayeredNavigation($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::SEOSUITE_CANONICAL_LAYERED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get suffix url for product
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
     * Get category suffix
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

    /**
     * Get product path
     *
     * @param string $storeId
     * @return mixed
     */
    public function getProductPath($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_PRODUCT_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
