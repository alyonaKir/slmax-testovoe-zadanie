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
 * @package    Bss_ConfigurableProductReviews
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _) {
    'use strict';
    return function (widget) {

        $.widget('mage.SwatchRenderer', widget, {

            // /**
            //  * Event for swatch options
            //  *
            //  * @param {Object} $this
            //  * @param {Object} $widget
            //  * @private
            //  */
            // _OnClick: function ($this, $widget) {
            //
            //     $widget._super($this, $widget);
            //
            //     $widget._UpdateCustomPriceDisplay();
            // },
            //
            // /**
            //  * Event for select
            //  *
            //  * @param {Object} $this
            //  * @param {Object} $widget
            //  * @private
            //  */
            // _OnChange: function ($this, $widget) {
            //
            //     $widget._super($this, $widget);
            //
            //     $widget._UpdateCustomPriceDisplay();
            // },
            //
            // _UpdateCustomPriceDisplay: function () {
            //     var $widget = this,
            //         $product = $widget.element.parents($widget.options.selectorProduct),
            //         $productPrice = $product.find(this.options.selectorProductPrice),
            //         options = _.object(_.keys($widget.optionsMap), {}),
            //         result,
            //         tierPriceHtml;
            //
            //     $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
            //         var attributeId = $(this).attr('attribute-id');
            //
            //         options[attributeId] = $(this).attr('option-selected');
            //     });
            //
            //     result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];
            //
            //     if (typeof result == 'undefined') {
            //         $(this.options.slyOldPriceSelector).show();
            //     } else {
            //         if (result.oldPrice.amount !== result.finalPrice.amount) {
            //             $(this.options.slyOldPriceSelector).show();
            //         } else {
            //             $(this.options.slyOldPriceSelector).hide();
            //         }
            //     }
            // },
            _UpdatePrice: function () {
                var $widget = this,
                    $product = $widget.element.parents($widget.options.selectorProduct),
                    $productPrice = $product.find(this.options.selectorProductPrice),
                    options = _.object(_.keys($widget.optionsMap), {}),
                    result,
                    tierPriceHtml;

                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    var attributeId = $(this).attr('attribute-id');

                    options[attributeId] = $(this).attr('option-selected');
                });

                result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

                $productPrice.trigger(
                    'updatePrice',
                    {
                        'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                    }
                );

                if (typeof result == 'undefined') {
                    $(this.options.slyOldPriceSelector).show();
                } else {
                    if (result.oldPrice.amount !== result.finalPrice.amount) {
                        $(this.options.slyOldPriceSelector).show();
                    } else {
                        $(this.options.slyOldPriceSelector).hide();
                    }
                }

                // if (typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                //     $(this.options.slyOldPriceSelector).show();
                // } else {
                //     $(this.options.slyOldPriceSelector).hide();
                // }

                if (typeof result != 'undefined' && result.tierPrices.length) {
                    if (this.options.tierPriceTemplate) {
                        tierPriceHtml = mageTemplate(
                            this.options.tierPriceTemplate,
                            {
                                'tierPrices': result.tierPrices,
                                '$t': $t,
                                'currencyFormat': this.options.jsonConfig.currencyFormat,
                                'priceUtils': priceUtils
                            }
                        );
                        $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                    }
                } else {
                    $(this.options.tierPriceBlockSelector).hide();
                }

                $(this.options.normalPriceLabelSelector).hide();

                _.each($('.' + this.options.classes.attributeOptionsWrapper), function (attribute) {
                    if ($(attribute).find('.' + this.options.classes.optionClass + '.selected').length === 0) {
                        if ($(attribute).find('.' + this.options.classes.selectClass).length > 0) {
                            _.each($(attribute).find('.' + this.options.classes.selectClass), function (dropdown) {
                                if ($(dropdown).val() === '0') {
                                    $(this.options.normalPriceLabelSelector).show();
                                }
                            }.bind(this));
                        } else {
                            $(this.options.normalPriceLabelSelector).show();
                        }
                    }
                }.bind(this));
            },
        });

        return $.mage.SwatchRenderer;
    }
});
