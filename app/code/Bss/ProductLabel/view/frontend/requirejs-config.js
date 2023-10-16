/*
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
 *  @category  BSS
 *  @package   Bss_ProductLabel
 *  @author    Extension Team
 *  @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
var config = {
    map: {
        '*': {
            '*': {
                label: 'Bss_ProductLabel/js/label',
                productlabel: 'Bss_ProductLabel/js/label-product-page',
                productlistlabel: 'Bss_ProductLabel/js/label-category-page',
            },

            //change template sidebar-cart
            'Magento_Checkout/template/summary/item/details/thumbnail.html':
                'Bss_ProductLabel/template/summary/item/details/thumbnail.html',

            //change template minicart
            'Magento_Checkout/template/minicart/item/default.html':
                'Bss_ProductLabel/template/minicart/item/default.html',

            //change template widget
            'Magento_Catalog/template/product/list/columns/image_with_borders.html':
                'Bss_ProductLabel/template/catalog/product/image_with_borders.html',
        }
    }
};
