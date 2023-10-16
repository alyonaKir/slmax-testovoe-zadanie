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
 * @package    Bss_SeoReport
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
            countCrawled: 0,
            maxPage: 0,
            crawling: false
        },
        _create: function () {
            let dataCrawl = this.options.dataCrawl;
            let totalLink = dataCrawl.length;
            if (totalLink > 0) {
                this.crawlSite(dataCrawl, 0);
            }
        },
        crawlSite: function(dataSite, index) {
            let self = this;
            let urlCrawl = this.options.crawlLink;
            let lastKey = dataSite.length - 1;
            if (index > lastKey) {
                return false;
            }
            $.each(dataSite, function(key, value) {
                if (Number(key) === Number(index)) {
                    let urlToCrawl = value.main_url + value.path;
                    $.ajax({
                        showLoader: false,
                        url: urlCrawl,
                        data : {
                            main_url: value.main_url,
                            path: value.path
                        },
                        type: "POST",
                        dataType: 'json',
                        complete: function(response) {
                            self.crawlSite(dataSite, Number(index + 1));
                        },
                        error: function() {
                            self.crawlSite(dataSite, Number(index + 1));
                        }
                    });
                }
            });
        }
    });
    return $.bss.crawl_site;
});
