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

    jQuery.widget('bss.product_label', {
        _create: function () {
            /**
             * Handle for swatch display
             */
            var self = this,
                selector = ".fotorama__stage",
                selectorProductList = ".product-image-container";
            if (this.options.selector) {
                selector = this.options.selector;
            }
            if (this.options.selectorProductList) {
                selectorProductList = this.options.selectorProductList;
            }

            if (jQuery(selector).length > 0) {
                appendLabel(selector);
            } else {
                jQuery(document).on('gallery:loaded', function () { // add additional event here
                    if (jQuery(selector).length > 0) {
                        appendLabel(selector);
                    }
                });
            }

            function appendLabel(selector) {
                jQuery(selector).css('position', 'relative');
                if (jQuery('.product-info-main').length > 0 && jQuery('.product-info-main .bss-label-productlist').length > 0) {
                    jQuery('.product-info-main .bss-label-productlist').appendTo(jQuery(selector));
                }
                jQuery(selector).find('.bss-label-productlist').show();
            }

            /**
             * Handle for swatch display
             */
            jQuery('body').on('click', '.swatch-option', function () {
                if (jQuery(this).closest('.product-options-wrapper').find('.selected').length > 0) {
                    self.activeChildLabel();
                } else {
                    self.activeLabel();
                }

                var selected_options = jQuery(this).closest('.product-options-wrapper').find('select');
                if (jQuery(this).closest('.product-options-wrapper').find('.selected').length == 0
                    && (selected_options.length > 0 && selected_options[0].value == '')) {
                    self.activeLabel();
                }
            });

            /**
             * Append Label to bellow Product list
             */
            jQuery(document).ready(function () {
                jQuery('.bss-label-productlist').each(function (index) {
                    jQuery(this).closest('.product-item').find(selectorProductList).append(jQuery(this));
                })
                jQuery(selectorProductList).css('position', 'relative');
            });

            /**
             * Fix conflict with module BSS: Simple Details on Configurable Product & Configurable Product Wholesale Display
             */
            jQuery('body').on('click', '.bss-table-row-attr', function () {
                var productId = jQuery(this).closest('.bss-table-row').find('.bss-qty-col .bss-qty').attr('data-product-id');
                self.activeLabel(productId);
            });

            /**
             * Handle for both dropdown display
             */
            jQuery(".product-options-wrapper select[id^='attribute']").on('change', function () {
                var selectsValues = jQuery(".product-options-wrapper select[id^='attribute']");
                setTimeout(function () {
                    if (selectsValues[0].value !== "" && selectsValues[1].value !== "") {
                        var simpleId = jQuery("input[name=selected_configurable_option]").val();
                        if (simpleId == '') {
                            simpleId = null;
                        }
                        self.activeLabel(simpleId);
                    } else {
                        if (selectsValues[0].value == "" && selectsValues[1].value == "") {
                            self.activeLabel();
                        }
                    }
                }, 200)
            });
        },

        // Find product id by attribute then active it
        activeChildLabel: function () {
            var selected_options = {};
            var self = this;
            jQuery('.main div.swatch-attribute').each(function (k, v) {
                var attribute_id = jQuery(v).attr('attribute-id');
                var option_selected = jQuery(v).attr('option-selected');
                if (!attribute_id || !option_selected) {
                    return;
                }
                selected_options[attribute_id] = option_selected;
            });

            // find swatchWidgetName for finding 'jsonconfig' data if other module has override Magento-swatches
            var swatchWidgetName = null;
            var swatchElement = jQuery('[data-role=swatch-options]');
            for (var key in swatchElement.data()) {
                // skip loop if the property is from prototype
                if (!swatchElement.data().hasOwnProperty(key)) continue;
                var obj = swatchElement.data()[key];
                for (var prop in obj) {
                    // skip loop if the property is from prototype
                    if (obj.hasOwnProperty('options')) {
                        swatchWidgetName = key;
                    }
                }
            }

            if (swatchWidgetName === null) {
                return;
            }
            if (swatchElement.data(swatchWidgetName).options.hasOwnProperty('jsonConfig')) {
                var product_id_index = swatchElement.data(swatchWidgetName).options.jsonConfig.index;
            } else {
                var product_id_index = swatchElement.data(swatchWidgetName).options.swatchOptions.index;
            }


            var found_id = null;
            jQuery.each(product_id_index, function (product_id, attributes) {
                var productIsSelected = function (attributes, selected_options) {
                    return _.isEqual(attributes, selected_options);
                }
                if (productIsSelected(attributes, selected_options)) {
                    found_id = product_id;
                }
            });
            self.activeLabel(found_id);
        },

        activeLabel: function (product_id) {
            jQuery('.media .label-image').hide();
            jQuery('.media .label-image').attr('data-display', 'none');
            if (product_id != null || typeof product_id != 'undefined') {
                var query = 'data-product-id=' + product_id;
                jQuery('.media .label-image[' + query + ']').fadeIn('fast');
                // avoid showing label after ajax, check: label.js, ajaxSuccess function
                jQuery('.media .label-image[' + query + ']').attr('data-display', 'block');
            } else {
                jQuery('.media .label-image[is-main-product="true"]').fadeIn('fast');
                jQuery('.media .label-image[is-main-product="true"]').attr('data-display', 'block');
            }
        }

    });

    return jQuery.bss.product_label;
});
