<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Model\Config\Source;

/**
 * Class PageDisplayLabel
 * @package Bss\ProductLabel\Model\Config\Source
 */
class PageDisplayLabel
{
    /**
     * @return array
     */
    public static function toOptionArray()
    {
        return [
            ['value' => 'cms_index_index', 'label' => __('Home Page')],
            ['value' => 'catalog_category_view', 'label' => __('Catalog Page')],
            ['value' => 'catalog_product_view', 'label' => __('Product Page')],
            ['value' => 'checkout_cart_index', 'label' => __('Shopping Cart Page')],
            ['value' => 'checkout_index_index', 'label' => __('Checkout Page')],
            ['value' => 'catalogsearch_result_index', 'label' => __('Search Page')],
            ['value' => 'wishlist_index_index', 'label' => __('Wishlist Page')],
            ['value' => 'catalog_product_compare_index', 'label' => __('Compare Page')],
        ];
    }
}
