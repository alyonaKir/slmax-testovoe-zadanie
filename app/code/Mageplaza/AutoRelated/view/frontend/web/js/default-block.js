/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'mage/storage',
    'Mageplaza_AutoRelated/js/model/impression',
    'jquery/ui',
    'owlCarousel'
], function ($, storage, impressionModel) {
    'use strict';

    $.widget('mageplaza.arp_default_block', {
        options: {
            type: '',
            rule_id: '',
            location: '',
            mode: ''
        },

        /**
         * @private
         */
        _create: function () {
            this.initSlider();
            this.initObserver();

            if (this.options.mode === '1') {
                impressionModel.registerRuleImpression(this.options.rule_id);
            }
        },

        /**
         * generate owl carousel slider
         */
        initSlider: function () {
            var slidesToShow     = this.options.number_product_slider,
                sliderWidth      = this.options.slider_config.slider_width,
                sliderHeight     = this.options.slider_config.slider_height,
                slideNav         = this.options.slider_config.show_next_prev > 0,
                sliderNavText    = slideNav ? ['‹', '›'] : ['', ''],
                slideDot         = this.options.slider_config.show_dots_nav > 0,
                slideAutoplay    = this.options.slider_config.slider_autoplay > 0,
                slideAutoTimeout = slideAutoplay ? this.options.slider_config.auto_timeout : 1000,
                responsive       = {
                    1028: {
                        items: this.options.number_product_slider
                    },
                    640: {
                        items: 3
                    },
                    0: {
                        items: 2
                    }
                };

            if (!this.isSlider()) {
                return this;
            }

            if (this.options.location.indexOf('sidebar') !== -1) {
                slidesToShow = 1;
                responsive   = {};
            } else if (this.options.location.indexOf('cross') !== -1) {
                slidesToShow = 4;
            }

            this.element.find('ol').owlCarousel({
                items: slidesToShow,
                rtl: false,
                loop: true,
                autoplay: slideAutoplay,
                nav: slideNav,
                navText: sliderNavText,
                dots: slideDot,
                autoplaySpeed: slideAutoTimeout,
                slideBy: this.options.number_product_scrolled,
                responsive: responsive,
                autoplayHoverPause: true
            });

            if (sliderWidth.length || sliderHeight.width) {
                this.formatSlider(sliderWidth, sliderHeight);
            }
        },

        formatSlider: function (sliderWidth, sliderHeight) {
            var sliderContent = $('.mageplaza-autorelated-content'),
                sliderBlock   = $('.mp-arp-slider-content');

            if (!/Android|webOS|iPhone|Mac|Macintosh|iPod|BlackBerry|IEMobile|Opera Mini|Ipad/i.test(navigator.userAgent)) {
                sliderContent.css('margin', '10px');
                if (sliderWidth.length) {
                    sliderBlock.css('width', sliderWidth + 'px');
                }
                if (sliderHeight.length) {
                    sliderBlock.css('height', sliderHeight + 'px');
                    sliderBlock.css('overflow', 'hidden');
                }
            }
        },

        /**
         * init click observer
         */
        initObserver: function () {
            var clickEl = this.element.find(
                '.mageplaza-autorelated-slider .product-item .slider-product-item-info a, ' +
                '.mageplaza-autorelated-slider .product-item .slider-product-item-info button, ' +
                '.block-content .products-grid .product-item .slider-product-item-info a, ' +
                '.block-content .products-grid .product-item .slider-product-item-info button, ' +
                '.popup-content .popup-right a, ' +
                '.popup-content .popup-right button'
            );

            if (this.isSlider()) {
                clickEl.draggable({
                    start: function () {
                        $(this).addClass('noclick');
                    }
                });
            }

            clickEl.click(function () {
                var id = $(this).parents('.mageplaza-autorelated-block').attr('rule-id');

                if ($(this).hasClass('noclick')) {
                    $(this).removeClass('noclick');
                }
                storage.post('autorelated/ajax/click', JSON.stringify({ruleId: id}), false);
            });
        },

        isSlider: function () {
            return this.options.type === 'slider';
        }
    });

    return $.mageplaza.arp_default_block;
});
