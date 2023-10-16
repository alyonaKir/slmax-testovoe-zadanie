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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';
    $.widget('bss.crawl_site', {
        config: {
            linksPage: 1,
            countCrawled: 0,
            countSuccessfully: 0,
            countFalse: 0,
            maxPage: 0,
            crawling: false
        },
        _create: function () {
            let totalLink = this.options.totalLink;
            let maxLink = this.options.maxLink;
            this.config.maxPage = Math.ceil(totalLink/maxLink);
            if (!this.options.statusEnable) {
                $("#bss_seo_report_crawl_lost").addClass("disable");
                $("#bss_seo_report_start_crawl").addClass("disable");
                return false;
            }
            let self = this;
            $(document).on('click', "#bss_seo_report_start_crawl", function() {
                if (!self.config.crawling) {
                    self.handleCrawlUrl('all');
                }
            });
            $(document).on('click', "#bss_seo_report_crawl_lost", function() {
                if (!self.config.crawling) {
                    self.handleCrawlUrl('lost');
                }
            });
        },
        handleCrawlUrl: function(type) {
            let self = this;
            self.config.crawling = true;
            $("#bss_seo_report_crawl_lost").addClass("disable");
            $("#bss_seo_report_start_crawl").addClass("disable");
            let urlAjax = this.options.ajaxLink;
            if (this.config.linksPage > this.config.maxPage) {
                self.config.crawling = false;
                $("#bss_seo_report_crawl_lost").removeClass("disable");
                $("#bss_seo_report_start_crawl").removeClass("disable");
                self.renderFinishView();
                return false;
            }
            $.ajax({
                showLoader: false,
                url: urlAjax,
                data : {
                    page: self.config.linksPage
                },
                type: "POST",
                dataType: 'json',
                complete: function(response) {
                    let result = response.responseText;
                    try {
                        result = JSON.parse(result);
                        self.crawlSite(result.data, 0);
                    } catch (err) {
                    }
                },
                error: function() {
                }
            });
        },
        crawlSite: function(dataSite, index) {
            let self = this;
            let urlCrawl = this.options.crawlLink;
            let lastKey = dataSite.length - 1;
            if (index > lastKey) {
                this.config.linksPage++;
                this.handleCrawlUrl();
                return false;
            }
            self.config.countCrawled++;
            $.each(dataSite, function(key, value) {
                if (Number(key) === Number(index)) {
                    let productName = value.name;
                    $.ajax({
                        showLoader: false,
                        url: urlCrawl,
                        data : {
                            product_id: value.id,
                            type: 'generate'
                        },
                        type: "POST",
                        dataType: 'json',
                        complete: function(response) {
                            let result = response.responseText;
                            try {
                                result = JSON.parse(result);
                                if (typeof result.type_error !== 'undefined' && result.type_error === 'excluded') {
                                    self.renderCrawlView(productName, 'excluded');
                                } else {
                                    self.renderCrawlView(productName, result.status);
                                }

                                self.crawlSite(dataSite, Number(index + 1));
                            } catch (err) {
                            }
                        },
                        error: function() {
                            self.renderCrawlView(productName, false);
                            self.crawlSite(dataSite, Number(index + 1));
                        }
                    });
                }
            });
        },
        renderFinishView: function() {
            let textDOM = $.mage.__('Finish Generation - Total: ') + this.config.countCrawled + $.mage.__(' - Success: ') + this.config.countSuccessfully + $.mage.__(' - False: ') + this.config.countFalse;
            let itemAddDOM = '<div class="item_show_crawl">' + textDOM + '</div>';
            $("#seo_report_show_crawl_child").append(itemAddDOM);
            let heightToScroll = $("#seo_report_show_crawl_child").height()*1.3;
            $(".seo_report_show_crawl, #seo_report_show_crawl_child").animate({ scrollTop: heightToScroll});
            this.config.countCrawled = 0;
            this.config.countSuccessfully = 0;
            this.config.countFalse = 0;
            this.config.linksPage = 1;
        },
        renderCrawlView: function(productName, status) {
            let statusText = $.mage.__('Generate Success');
            if (status === true || status === 'excluded') {
                this.config.countSuccessfully++;
                if (status === 'excluded') {
                    statusText = $.mage.__('Excluded from SEO Alt Text');
                }
            } else {
                this.config.countFalse++;
                statusText = $.mage.__('Generate False');
            }
            let percent = (this.config.countCrawled/this.options.totalLink)*100;
            percent = Math.round(percent * 10) / 10;
            $("#seo_report_percent_crawl").css({"width": percent + "%"});
            let textDOM = '[' + percent + '%]  ' + statusText + ': ' + productName;
            let itemAddDOM = '<div class="item_show_crawl">' + textDOM + '</div>';
            $("#seo_report_show_crawl_child").append(itemAddDOM);
            let heightToScroll = $("#seo_report_show_crawl_child").height()*1.3;
            $(".seo_report_show_crawl, #seo_report_show_crawl_child").animate({ scrollTop: heightToScroll});
        }
    });
    return $.bss.crawl_site;
});
