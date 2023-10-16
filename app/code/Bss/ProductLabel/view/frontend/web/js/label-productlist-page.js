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

define([
    'jquery',
    'underscore',
    'domReady!'
], function (jQuery, _) {
    'use strict';

    jQuery.widget('bss.category_label', {
        _create: function () {
            var self = this,
                selector = ".product-image-container";
            if (this.options.selector) selector = this.options.selector;

            /**
             * Handle for swatch display
             */
            jQuery('body').on('click', '[class^=swatch-opt-] .swatch-option', function () {
                var parent = jQuery(this).closest('[class^=swatch-opt-]');
                if (parent.find('.selected').length > 0) {
                    self.activeChildLabel(parent);
                } else {
                    self.activeLabel(parent);
                }
            });

            /**
             * Append Label to correct wrapper
             */
            function appendLabel() {
                jQuery('.bss-label-productlist').each(function(index) {
                    /* Compare Page */
                    if (jQuery('.catalog-product-compare-index').length
                        || jQuery('.catalog-product_compare-index').length // magento 2.3
                    ) {
                        jQuery(this).closest('td').find(selector).append(jQuery(this));
                    }
                    /* Cart Page */
                    if (jQuery('.checkout-cart-index').length) {
                        jQuery(this).parents('.cart.item').find(selector).append(jQuery(this));
                    }
                    /* Other Page */
                    jQuery(this).closest('.product-item').find(selector).append(jQuery(this));
                });
                jQuery(selector).css('position', 'relative');
                jQuery(selector).find('.bss-label-productlist').show();
            }
            jQuery(document).ready(function() {
                appendLabel();
            });
            // run again after contentUpdated event triggered (Eg. wishlist sidebar loaded)
            jQuery('body').on('contentUpdated', function() {
                appendLabel();
            });
        },

        // Find product id by attribute then active it
        activeChildLabel: function (wrapper) {
            var self = this;
            var selected_options = {};
            wrapper.find('div.swatch-attribute').each(function (k, v) {
                var attribute_id = jQuery(v).attr('attribute-id');
                var option_selected = jQuery(v).attr('option-selected');
                if (!attribute_id || !option_selected) {
                    return;
                }
                selected_options[attribute_id] = option_selected;
            });
            var product_id_index = wrapper.data('mageSwatchRenderer').options.jsonConfig.index;
            var found_id = null;
            jQuery.each(product_id_index, function (product_id, attributes) {
                var productIsSelected = function (attributes, selected_options) {
                    return _.isEqual(attributes, selected_options);
                }
                if (productIsSelected(attributes, selected_options)) {
                    found_id = product_id;
                }
            });

            self.activeLabel(wrapper, found_id);
        },

        activeLabel: function(refElement, product_id) {
            if (refElement != null) {
                var productWrapper = refElement.closest('.product-item-info');
                productWrapper.find('.label-image').hide();
                // avoid showing label after ajax, check: label.js, ajaxSuccess function
                productWrapper.find('.label-image').attr('data-display', 'none');
                if (product_id != null || typeof product_id != 'undefined') {
                    var query = 'data-product-id=' + product_id;
                    productWrapper.find('.label-image[' + query + ']').fadeIn('fast');
                    productWrapper.find('.label-image[' + query + ']').attr('data-display', 'block');
                } else {
                    productWrapper.find('.label-image[is-main-product="true"]').fadeIn('fast');
                    productWrapper.find('.label-image[is-main-product="true"]').attr('data-display', 'block');
                }
            }
        }
    });

    return jQuery.bss.category_label;
});
