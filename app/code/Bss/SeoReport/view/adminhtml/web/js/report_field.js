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
    $.widget('bss.report_field', {
        config: {
            auditView: '',
            firstRender: true,
            firstSearchConsole: true,
            auditSEO: {
                descriptionCount: 0,
                keywordDescriptionCount: 0,
                keywordMetaTitleCount: 0,
                keywordMetaDescriptionCount: 0,
                keywordUrlKeyCount: 0,
                outboundLinkCount: 0,
                keywordFirstParagraphCount: 0,
                keywordDensityPercent: 0
            },
            searchConsoleDOM: {
                mainKeyword: '',
                metaKeyword: '',
                pageUrl: ''
            },
            setTimeout: {
                mainKeyword: '',
                metaKeyword: ''
            },
            statusError: false,
            messageError: ''
        },
        _create: function () {
            let options = this.options;
            let self = this;
            //Handle for Meta Title and Meta Description
            self.processMeta();
            //Handle Audit SEO
            self.processAuditSEO();
            self.handleFirstRenderAudit();
            self.handleFirstRenderSearchConsole();
        },
        processMeta: function () {
            let self = this;
            $(document).on('keyup', '[data-index=meta_title]', function(e) {
                let metaTitle = $(e.target).val();
                let percentMeta = 0;
                let colorMeta = 'red';
                if (metaTitle.length <= 70) {
                    percentMeta = Number((metaTitle.length/70)*100);
                    if (metaTitle.length >= 50) {
                        colorMeta = 'green';
                    } else {
                        colorMeta = 'orange'
                    }
                } else {
                    percentMeta = 100;
                }
                let metaReportItem = $('#meta_title_report .meta_report_item');
                metaReportItem.css({"width": percentMeta + "%"});
                metaReportItem.removeClass('green');
                metaReportItem.removeClass('red');
                metaReportItem.removeClass('orange');
                metaReportItem.addClass(colorMeta);

                //Handle Seo Audit
                self.options.metaTitle = metaTitle;
                self.handleMetaTitle();
                self.handleAuditSEO();
                self.finishRenderAuditView();
            });
            $(document).on('keyup', '[data-index=meta_description]', function(e) {
                let metaDescription = $(e.target).val();
                let percentMeta = 0;
                let colorMeta = 'red';

                if (metaDescription.length < 100) {
                    percentMeta = Number((metaDescription.length/255)*100);
                }
                if (metaDescription.length >= 100 && metaDescription.length < 200) {
                    percentMeta = Number((metaDescription.length/255)*100);
                    colorMeta = 'orange';
                }
                if (metaDescription.length >= 200 && metaDescription.length <= 255) {
                    percentMeta = Number((metaDescription.length/255)*100);
                    colorMeta = 'green';
                }
                if (metaDescription.length > 255) {
                    percentMeta = 100;
                    colorMeta = 'red';
                }

                let metaReportItem = $('#meta_description_report .meta_report_item');
                metaReportItem.css({"width": percentMeta + "%"});
                metaReportItem.removeClass('green');
                metaReportItem.removeClass('red');
                metaReportItem.removeClass('orange');
                metaReportItem.addClass(colorMeta);

                //Handle Seo Audit
                self.options.metaDescription = metaDescription;
                self.handleMetaDescription();
                self.handleAuditSEO();
                self.finishRenderAuditView();
            });
            $(document).on('keyup', '[data-index=url_key]', function(e) {
                self.options.urlKey = $(e.target).val();
                self.handleUrlKey();
                self.handleAuditSEO();
                self.finishRenderAuditView();
            });

            $(document).on('keyup', '[data-index=identifier]', function(e) {
                self.options.urlKey = $(e.target).val();
                self.handleUrlKey();
                self.handleAuditSEO();
                self.finishRenderAuditView();
            });

            $(document).on('keyup', '[data-index=main_keyword]', function(e) {
                self.options.mainKeyword = $(e.target).val();
                self.handleDescription();
                self.handleMetaDescription();
                self.handleMetaTitle();
                self.handleUrlKey();

                self.handleAuditSEO();
                self.finishRenderAuditView();

                clearTimeout(self.config.setTimeout.mainKeyword);
                self.config.setTimeout.mainKeyword = setTimeout(function() {
                    self.prepareSearchConsoleDOM('main_keyword');
                }, 1500);
            });

            $(document).on('keyup', '[data-index=meta_keyword]', function(e) {
                clearTimeout(self.config.setTimeout.metaKeyword);
                self.options.metaKeyword = $(e.target).val();
                self.config.setTimeout.metaKeyword = setTimeout(function() {
                    self.prepareSearchConsoleDOM('meta_keyword');
                }, 1500);
            });

            $(document).on('keyup', '[data-index=meta_keywords]', function(e) {
                clearTimeout(self.config.setTimeout.metaKeyword);
                self.options.metaKeyword = $(e.target).val();
                self.config.setTimeout.metaKeyword = setTimeout(function() {
                    self.prepareSearchConsoleDOM('meta_keyword');
                }, 1500);
            });

            $(document).on('change', '[data-index=description] textarea', function(e) {
                self.options.description = $(e.target).val();
                self.handleDescription();
                self.handleMetaDescription();
                self.handleMetaTitle();
                self.handleUrlKey();

                self.handleAuditSEO();
                self.finishRenderAuditView();
            });

            $(document).on('change', 'textarea#cms_page_form_content', function(e) {
                self.options.description = $(e.target).val();
                self.handleDescription();
                self.handleMetaDescription();
                self.handleMetaTitle();
                self.handleUrlKey();

                self.handleAuditSEO();
                self.finishRenderAuditView();
            });
        },
        processAuditSEO: function () {
            let description = this.options.description;
            let metaDescription = this.options.metaDescription;
            let metaTitle = this.options.metaTitle;
            let urlKey = this.options.urlKey;
            let mainKeyword = this.options.mainKeyword;

            this.handleDescription();
            this.handleMetaDescription();
            this.handleMetaTitle();
            this.handleUrlKey();

            this.handleAuditSEO();
        },
        handleUrlKey: function() {
            let urlKey = this.options.urlKey;
            let mainKeyword = this.options.mainKeyword;
            if (mainKeyword === null || mainKeyword === '' || urlKey === null || urlKey === '') {
                this.config.auditSEO.keywordUrlKeyCount = 0;
            } else {
                let mainKeywordLower = mainKeyword.toLowerCase();
                let mainKeywordSlug = this.convertToSlug(mainKeywordLower);
                this.config.auditSEO.keywordUrlKeyCount = this.substrCount(urlKey, mainKeywordSlug);
            }
        },
        handleMetaTitle: function() {
            let metaTitle = this.options.metaTitle;
            let mainKeyword = this.options.mainKeyword;
            if (mainKeyword === null || mainKeyword === '' || metaTitle === null || metaTitle === '') {
                this.config.auditSEO.keywordMetaTitleCount = 0;
            } else {
                let mainKeywordLower = mainKeyword.toLowerCase();
                let metaTitleLower = metaTitle.toLowerCase();
                this.config.auditSEO.keywordMetaTitleCount = this.substrCount(metaTitleLower, mainKeywordLower);
            }
        },
        handleMetaDescription: function() {
            let metaDescription = this.options.metaDescription;
            let mainKeyword = this.options.mainKeyword;
            if (mainKeyword === null || mainKeyword === '' || metaDescription === null || metaDescription === '') {
                this.config.auditSEO.keywordMetaDescriptionCount = 0;
            } else {
                let mainKeywordLower = mainKeyword.toLowerCase();
                let metaDescriptionLower = metaDescription.toLowerCase();
                this.config.auditSEO.keywordMetaDescriptionCount = this.substrCount(metaDescriptionLower, mainKeywordLower);
            }
        },
        handleDescription: function() {
            let description = this.options.description;
            let mainKeyword = this.options.mainKeyword;
            let productUrl = this.options.productUrl;
            let descriptionFresh = $(description).text();
            if (descriptionFresh) {
                this.config.auditSEO.descriptionCount = descriptionFresh.split(' ').length;
            } else {
                this.config.auditSEO.descriptionCount = 0
            }

            //If not have Description Only
            if (description === null || description === '') {
                this.config.auditSEO.outboundLinkCount = 0;
            } else {
                let urlObject = this.getUrlObject(productUrl);
                let urlHostname = urlObject.hostname;

                let descriptionLinks = $(description).find('a').map(function(){
                    return $(this).attr('href');
                }).get();

                let self = this;
                let countOutboundLink = 0;
                if (descriptionLinks) {
                    for(let index in descriptionLinks) {
                        let urlLink = descriptionLinks[index];
                        let urlLinkObject = self.getUrlObject(urlLink);
                        if (urlLinkObject.hostname !== urlHostname) {
                            countOutboundLink++;
                        }
                    }
                }
                this.config.auditSEO.outboundLinkCount = countOutboundLink;
            }

            //If not have Main keyword or Description
            if (mainKeyword === null || mainKeyword === '' || description === null || description === '') {
                this.config.auditSEO.keywordDescriptionCount = 0;
                this.config.auditSEO.keywordDensityPercent = 0;
                this.config.auditSEO.keywordFirstParagraphCount = 0;
            } else {
                //Keyword Description Count
                let descriptionLower = descriptionFresh.toLowerCase();
                let mainKeywordLower = mainKeyword.toLowerCase();
                this.config.auditSEO.keywordDescriptionCount = this.substrCount(descriptionLower, mainKeywordLower);

                //Main Keyword in First Paragraph
                let firstParagraph = $(description).find("p").andSelf().filter("p:first").first().text();
                let firstParagraphLower = firstParagraph.toLowerCase();
                this.config.auditSEO.keywordFirstParagraphCount = this.substrCount(firstParagraphLower, mainKeywordLower);

                //Keyword Density Percent
                let keywordDensityPercent = 0;
                if (this.config.auditSEO.descriptionCount > 0) {
                    keywordDensityPercent = (this.config.auditSEO.keywordDescriptionCount/this.config.auditSEO.descriptionCount)*100;
                }
                keywordDensityPercent = Math.round(keywordDensityPercent * 100) / 100;
                this.config.auditSEO.keywordDensityPercent = keywordDensityPercent;
            }
        },
        renderAuditSEO: function (greenObject, redObject) {
            let domRedObject = '';
            let domGreenObject = '';
            let self = this;
            $(redObject).each(function(indexRed) {
                let typeRed = redObject[indexRed];
                let textRed = self.getTextReportAudit(typeRed, 'red');
                let htmlRed = '<div class="bss_seo_report_right_audit_item red"><svg viewBox="0 0 512 512"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg> '+ textRed + '</div>';
                domRedObject = domRedObject + htmlRed;
            });

            $(greenObject).each(function(indexGreen) {
                let typeGreen = greenObject[indexGreen];
                let textGreen = self.getTextReportAudit(typeGreen, 'green');
                let htmlGreen = '<div class="bss_seo_report_right_audit_item green"><svg viewBox="0 0 512 512"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg> '+ textGreen + '</div>';
                domGreenObject = domGreenObject + htmlGreen;
            });
            this.config.auditView = domRedObject + domGreenObject;

        },
        handleFirstRenderSearchConsole: function() {
            let self = this;
            let timeOutRender = '';
            $(document).on('DOMNodeInserted', ".google_search_console_view", function() {
                if (self.config.firstSearchConsole) {
                    clearTimeout(timeOutRender);
                    timeOutRender = setTimeout(function() {
                        self.config.firstSearchConsole = false;
                        self.prepareSearchConsoleDOM('main_keyword');
                        self.prepareSearchConsoleDOM('meta_keyword');
                        self.prepareSearchConsoleDOM('url_page');
                    }, 600);
                }
            });
        },
        handleFirstRenderAudit: function() {
            let self = this;
            let timeOutRender = '';
            $(document).on('DOMNodeInserted', ".seo_audit_report_view", function() {
                if (self.config.firstRender) {
                    clearTimeout(timeOutRender);
                    timeOutRender = setTimeout(function() {
                        self.config.firstRender = false;
                        self.finishRenderAuditView();
                    }, 600);
                }
            });
        },
        finishRenderAuditView: function() {
            $("#bss_seo_audit_view").html(this.config.auditView);
        },
        handleAuditSEO: function () {
            let greenObject = [];
            let redObject = [];
            if (this.config.auditSEO.keywordMetaTitleCount > 0) {
                greenObject.push('keyword_meta_title');
            } else {
                redObject.push('keyword_meta_title');
            }

            if (this.config.auditSEO.keywordMetaDescriptionCount > 0) {
                greenObject.push('keyword_meta_description');
            } else {
                redObject.push('keyword_meta_description');
            }

            if (this.config.auditSEO.outboundLinkCount > 0) {
                greenObject.push('outbound_link');
            } else {
                redObject.push('outbound_link');
            }

            if (this.config.auditSEO.keywordFirstParagraphCount > 0) {
                greenObject.push('first_paragraph');
            } else {
                redObject.push('first_paragraph');
            }

            if (this.config.auditSEO.keywordUrlKeyCount > 0) {
                greenObject.push('keyword_url');
            } else {
                redObject.push('keyword_url');
            }

            if (this.config.auditSEO.keywordDensityPercent >= 1 && this.config.auditSEO.keywordDensityPercent <= 3) {
                greenObject.push('density_percent');
            } else {
                redObject.push('density_percent');
            }

            if (this.config.auditSEO.descriptionCount > 300) {
                greenObject.push('description_count');
            } else {
                redObject.push('description_count');
            }
            this.renderAuditSEO(greenObject, redObject);
        },
        substrCount: function(string, substring) {
            if (substring.length === 0) {
                return 0;
            }
            if (string.length === 0) {
                return 0;
            }
            let c = 0;
            for (let i=0; i < string.length; i++)
            {
                if(substring === string.substr(i, substring.length))
                    c++;
            }
            return c;
        },
        getUrlObject: function(href) {
            let l = document.createElement("a");
            l.href = href;
            return l;
        },
        convertToSlug: function(slug) {
            slug = slug.toLowerCase();
            slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
            slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
            slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
            slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
            slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
            slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
            slug = slug.replace(/đ/gi, 'd');
            slug = slug.replace(/\\/gi, '');
            slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
            slug = slug.replace(/[^a-zA-Z ]/g, "");
            slug = '@' + slug + '@';
            slug = slug.replace(/\@\-|\-\@|\@/gi, '');
            slug = slug.replace(/\s{2,}/g, ' ');
            slug = slug.replace(/ /gi, "-");
            return slug;
        },
        getTextReportAudit: function (type, color) {
            let textReturn = '';
            if (type === 'keyword_meta_title') {
                if (color === 'green') {
                    textReturn = $.mage.__("Main keyword is included in meta title.");
                } else {
                    textReturn = $.mage.__("Main keyword isn't included in meta title.");
                }
            }
            if (type === 'keyword_meta_description') {
                if (color === 'green') {
                    textReturn = $.mage.__("Main keyword is included in meta description.");
                } else {
                    textReturn = $.mage.__("Main keyword isn't included in meta description.");
                }
            }

            if (type === 'keyword_description') {
                let keywordCountDescription = this.config.auditSEO.keywordDescriptionCount;
                if (color === 'green') {
                    textReturn = $.mage.__("Main keyword appears in description (") + keywordCountDescription + " times).";
                } else {
                    textReturn = $.mage.__("Main keyword does not appear in description (") + keywordCountDescription + " times).";
                }
            }

            if (type === 'keyword_url') {
                if (color === 'green') {
                    textReturn = $.mage.__('Main keyword should be contained in the page URL.');
                } else {
                    textReturn = $.mage.__('Main keyword is contained in the page URL.');
                }
            }

            if (type === 'description_count') {
                let descriptionCount = this.config.auditSEO.descriptionCount;
                if (color === 'green') {
                    textReturn = $.mage.__('Description should be more than 300 words (') + descriptionCount + ' words).';
                } else {
                    textReturn = $.mage.__('Description is less than 300 words (') + descriptionCount + ' word(s)).';
                }

            }
            if (type === 'outbound_link') {
                if (color === 'green') {
                    textReturn = $.mage.__('Outbound link(s) appear(s) in description.');
                } else {
                    textReturn = $.mage.__('No outbound link in description.');
                }
            }

            if (type === 'first_paragraph') {
                if (color === 'green') {
                    textReturn = $.mage.__("Main keyword appears in the first paragraph.");
                } else {
                    textReturn = $.mage.__("Main keyword does not appear in the first paragraph.");
                }
            }

            if (type === 'density_percent') {
                let keywordDensityPercent = this.config.auditSEO.keywordDensityPercent;
                textReturn = $.mage.__('Main keyword density is ') + keywordDensityPercent + '% (Recommended: 1-3%).';
            }
            return textReturn;
        },
        processSearchConsole: function(type, value) {
            let self = this;
            let searchConsoleUrl = this.options.searchConsoleUrl;
            let searchConsoleType = 'query';
            if (type === 'url_page') {
                searchConsoleType = 'page';
            }
            if (typeof value === 'undefined' || value === '' || value === null ) {
                self.emptySearchConsoleDOM(type);
                return false;
            }
            $.ajax({
                showLoader: false,
                url: searchConsoleUrl,
                data : {
                    value: value,
                    type: searchConsoleType
                },
                type: "POST",
                dataType: 'json',
                complete: function(response) {
                    let result = response.responseText;
                    try {
                        result = JSON.parse(result);
                        if (result.status) {
                            if (typeof result.data.rows !== 'undefined') {
                                self.completeSearchConsoleDOM(type, result.data.rows);
                            } else {
                                if (typeof result.data.error !== "undefined") {
                                    if (typeof result.data.error.errors[0].reason !== "undefined" && result.data.error.errors[0].reason === "invalidParameter") {
                                        self.config.statusError = true;
                                        self.config.messageError = $.mage.__("Invalid parameter when connecting to Google Search Console. Please check your domain in Google Search Console. ") + '<br/>' + $.mage.__("Go to") + ' <a href="https://search.google.com/search-console" target="_blank">' + $.mage.__("Google Search Console") + '</a>.';
                                    }
                                    if (typeof result.data.error.errors[0].reason !== "undefined" && result.data.error.errors[0].reason === "forbidden") {
                                        self.config.statusError = true;
                                        self.config.messageError = $.mage.__("You have not yet added your domain to Google Search Console account: ") + self.options.baseUrl + '. <br/>' + $.mage.__("Go to") + ' <a href="https://search.google.com/search-console" target="_blank">' + $.mage.__("Google Search Console") + '</a>.';
                                    }
                                }
                                self.emptySearchConsoleDOM(type);
                            }
                        } else {
                            if (result.error_type === 'permission') {
                                self.config.statusError = true;
                                self.config.messageError = $.mage.__("Please set up Google Authorization Code to connect Google Search Console.") + '<br/>' + $.mage.__("Go to") + ' <b>'  + $.mage.__("Store -> Configuration -> BSS Commerce SEO -> SEO Report ") + '</b> ' + $.mage.__("to set up.");
                            }
                            self.emptySearchConsoleDOM(type);
                        }
                    } catch (err) {
                        self.emptySearchConsoleDOM(type);
                    }
                },
                error: function() {
                    self.emptySearchConsoleDOM(type);
                }
            });
        },
        prepareSearchConsoleDOM: function (type) {
            let self = this;
            if (type === 'main_keyword') {
                //Set Loading
                let headerText = $.mage.__("Main Keyword: ") + self.options.mainKeyword;
                self.config.searchConsoleDOM.mainKeyword = self.getDOMConsoleItem(headerText, 'loading', []);
                self.processSearchConsole(type, self.options.mainKeyword);
            }
            if (type === 'meta_keyword') {
                //Set Loading
                let headerText = $.mage.__("Meta Keyword: ") + self.options.metaKeyword;
                self.config.searchConsoleDOM.metaKeyword = self.getDOMConsoleItem(headerText, 'loading', []);
                self.processSearchConsole(type, self.options.metaKeyword);
            }

            if (type === 'url_page') {
                //Set Loading
                let headerText = $.mage.__("Page URL: ") + self.options.productUrl;
                self.config.searchConsoleDOM.pageUrl = self.getDOMConsoleItem(headerText, 'loading', []);
                self.processSearchConsole(type, self.options.productUrl);
            }
            this.handleConsoleDOM();
        },
        completeSearchConsoleDOM: function(type, data) {
            let self = this;
            if (typeof data[0] === 'undefined') {
                self.emptySearchConsoleDOM(type);
            }

            if (type === 'main_keyword') {
                let headerText = $.mage.__("Main Keyword: ") + self.options.mainKeyword;
                self.config.searchConsoleDOM.mainKeyword = self.getDOMConsoleItem(headerText, 'render', data);
            }
            if (type === 'meta_keyword') {
                let headerText = $.mage.__("Meta Keyword: ") + self.options.metaKeyword;
                self.config.searchConsoleDOM.metaKeyword = self.getDOMConsoleItem(headerText, 'render', data);
            }

            if (type === 'url_page') {
                let headerText = $.mage.__("Page URL: ") + self.options.productUrl;
                self.config.searchConsoleDOM.pageUrl = self.getDOMConsoleItem(headerText, 'render', data);
            }
            this.handleConsoleDOM();
        },
        emptySearchConsoleDOM: function(type) {
            let self = this;
            if (type === 'main_keyword') {
                let headerText = $.mage.__("Main Keyword: ") + self.options.mainKeyword;
                self.config.searchConsoleDOM.mainKeyword = self.getDOMConsoleItem(headerText, 'empty', []);
            }
            if (type === 'meta_keyword') {
                let headerText = $.mage.__("Meta Keyword: ") + self.options.metaKeyword;
                self.config.searchConsoleDOM.metaKeyword = self.getDOMConsoleItem(headerText, 'empty', []);
            }

            if (type === 'url_page') {
                let headerText = $.mage.__("Page URL: ") + self.options.productUrl;
                self.config.searchConsoleDOM.pageUrl = self.getDOMConsoleItem(headerText, 'empty', []);
            }
            this.handleConsoleDOM();
        },
        handleConsoleDOM: function() {
            let self = this;
            let dataDOM = self.config.searchConsoleDOM.mainKeyword + self.config.searchConsoleDOM.metaKeyword + self.config.searchConsoleDOM.pageUrl;

            let DOMError = ' <div class="bss_google_search_console_item"><div class="bss_google_search_console_title">' + this.config.messageError + '</div></div>';
            if (!this.config.statusError) {
                $("#google_search_console_view").html(dataDOM);
            } else {
                $("#google_search_console_view").html(DOMError);
            }

        },
        getDOMConsoleItem: function (headerText, status, data) {
            let contentHtmlItem = '';
            $.each(data, function( index ) {
                let clicks = data[index].clicks;
                let ctr = Math.round(data[index].ctr * 100) / 100;
                let impressions = Math.round(data[index].impressions * 100) / 100;
                let position = Math.round(data[index].position * 100) / 100;
                let key = data[index].keys[0];
                contentHtmlItem = contentHtmlItem + '<tr><td width="20%"><p>' + key + '</p></td><td width="20%"><p>' + position + '</p></td><td width="20%"><p>' + impressions + '</p></td><td width="20%"><p> ' + clicks + ' </p></td><td width="20%"><p>' + ctr + '</p></td></tr>';
            });

            let returnDOM = '';
            let loadingHtmlItem = '<tr><td width="100%" colspan="5"><svg viewBox="0 0 100 100"><circle cx="50" cy="50" fill="none" stroke="#aaa" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138" transform="rotate(29.9412 50 50)"><animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 50;360 50 50" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform></circle></svg></td></tr>';
            let emptyHtmlItem = '<tr><td width="100%" colspan="5">' + $.mage.__("No Data") + '</td></tr>';

            let headerHtmlItem = '<tr class="active"><td width="20%"><p>Key</p></td><td width="20%"><p>Avg Position</p></td><td width="20%"><p>Impressions</p></td><td width="20%"><p>Clicks</p></td><td width="20%"><p>CTR</p></td></tr>';
            let topHtmlItem = '<div class="bss_google_search_console_item"><div class="bss_google_search_console_title"><b> ' + headerText + ' </b></div><table width="100%"><tbody>';

            let bottomHtmlItem = '</tbody></table></div>';
            if (status === 'loading') {
                returnDOM = topHtmlItem + headerHtmlItem + loadingHtmlItem + bottomHtmlItem;
            } else if (status === 'render') {
                returnDOM = topHtmlItem + headerHtmlItem + contentHtmlItem + bottomHtmlItem;
            } else {
                returnDOM = topHtmlItem + headerHtmlItem + emptyHtmlItem + bottomHtmlItem;
            }
            return returnDOM;
        }
    });
    return $.bss.report_field;
});
