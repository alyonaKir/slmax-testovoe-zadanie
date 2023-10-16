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
 * @package    Bss_SeoToolbar
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/translate'
], function ($) {
    $.widget('bss.seo_toolbar', {
        dataToolbar: {
            url: '',
            canonicalTag: '',
            title: '',
            metaTitle: '',
            metaDescription: '',
            metaKeyword: '',
            headings: {
                h1Elements: '',
                h2Elements: '',
                h3Elements: '',
                h4Elements: '',
                h5Elements: ''
            },
            imageObject: [],
            openGraph: {
                ogTitle: '',
                ogImage: '',
                ogDescription: '',
                ogUrl: '',
                ogType: ''
            },
            twitterCard: {
                twitterSite: '',
                twitterTitle: '',
                twitterDescription: '',
                twitterImage: ''
            }
        },
        statusHide: false,
        urlEdit: '',
        _create: function () {
            let self = this;
            $(document).ready(function() {
                let urlVars = self.getUrlVars();
                let cookieName = 'bss_seo_toolbar_token';
                if (typeof urlVars.token !== "undefined") {
                    let token = urlVars.token;
                    //Set to Cookie
                    self.createCookie(cookieName, token, 30);
                }
                //get Token from Cookie
                let cookieValue = self.getCookie(cookieName);

                let hideCookieName = 'bss_seo_toolbar_hide';
                let statusHideValue = self.getCookie(hideCookieName);
                self.statusHide = statusHideValue !== '' && statusHideValue !== null && Number(statusHideValue) === 1;
                self.handleToolbarStatus();
                self.handleClickToolbar();
                if (cookieValue !== '' && cookieValue !== null) {
                    //Show Loading
                    self.setData();
                    self.handleAjaxCheckAdmin(cookieValue);
                }
            });
        },
        handleAjaxCheckAdmin: function (token) {
            let ajaxUrl = this.options.ajaxUrl;
            let self = this;
            $.ajax({
                showLoader: false,
                url: ajaxUrl,
                data : {
                    token: token,
                    entity_id: self.options.entityId,
                    entity_type: self.options.entityType
                },
                type: "POST",
                dataType: 'json',
                complete: function(response) {
                    let result = response.responseText;
                    result = JSON.parse(result);
                    if (result.status) {
                        self.urlEdit = result.data.backend_url;
                        let DOMToolbar = self.getDOMToolbar();
                        self.renderToolbar(DOMToolbar);
                    }
                }
            });
        },
        getDOMToolbar: function() {
            let headerToolbar = this.getHeaderToolbar();
            let bodyToolbar = this.getBodyToolbar();
            return headerToolbar + bodyToolbar;
        },
        getHeaderToolbar: function() {
            return '<div class="bss_toolbar_header"><img src="' + this.options.logoUrl + '" alt="Logo"/><p>' + $.mage.__("SEO Toolbar") + '</p><svg viewBox="0 0 448 512"><path d="M441.9 167.3l-19.8-19.8c-4.7-4.7-12.3-4.7-17 0L224 328.2 42.9 147.5c-4.7-4.7-12.3-4.7-17 0L6.1 167.3c-4.7 4.7-4.7 12.3 0 17l209.4 209.4c4.7 4.7 12.3 4.7 17 0l209.4-209.4c4.7-4.7 4.7-12.3 0-17z"></path></svg></div>';
        },
        getBodyToolbar: function() {
            let firstBodyToolbar = '';
            if (this.statusHide) {
                firstBodyToolbar = '<div class="bss_toolbar_body hide_body">';
            } else {
                firstBodyToolbar = '<div class="bss_toolbar_body show_body">';
            }
            let lastBodyToolbar = '</div>';
            let urlDOM = this.getItemToolbar($.mage.__("URL:"), this.dataToolbar.url, "success", this.toolTipObject.url);

            //For Canonical Tag
            let statusCanonicalTag = "success";
            if (this.dataToolbar.canonicalTag === '' || this.dataToolbar.canonicalTag === null) {
                statusCanonicalTag = "false";
            }
            let canonicalTagDOM = this.getItemToolbar($.mage.__("Canonical Tag:"), this.dataToolbar.canonicalTag, statusCanonicalTag, this.toolTipObject.canonicalTag);

            //For Meta Title
            let statusMetaTitle = "false";
            if (this.dataToolbar.title === '' || this.dataToolbar.title === null) {
                statusMetaTitle = "false";
            } else {
                if (this.dataToolbar.title.length >= 50 && this.dataToolbar.title.length <= 70) {
                    statusMetaTitle = "success";
                }
                if (this.dataToolbar.title.length > 70) {
                    statusMetaTitle = "false"
                }
                if (this.dataToolbar.title.length < 50) {
                    statusMetaTitle = "warming";
                }
            }
            let titleDOM = this.getItemToolbar($.mage.__("Title:"), this.dataToolbar.title, statusMetaTitle, this.toolTipObject.title);

            //For Meta Description
            let statusMetaDescription = "false";
            if (this.dataToolbar.metaDescription === '' || this.dataToolbar.metaDescription === null) {
                statusMetaDescription = "false";
            } else {
                if (this.dataToolbar.metaDescription.length >= 200 && this.dataToolbar.metaDescription.length <= 255) {
                    statusMetaDescription = "success";
                }
                if (this.dataToolbar.metaDescription.length > 255) {
                    statusMetaDescription = "false"
                }
                if (this.dataToolbar.metaDescription.length >= 100 && this.dataToolbar.metaDescription.length < 200) {
                    statusMetaDescription = "warming";
                }
            }
            let metaDescriptionDOM = this.getItemToolbar($.mage.__("Meta Description:"), this.dataToolbar.metaDescription, statusMetaDescription, this.toolTipObject.description);

            //For Meta Keyword
            let statusMetaKeywords = "success";
            if (this.dataToolbar.metaKeyword === '' || this.dataToolbar.metaKeyword === null) {
                statusMetaKeywords = "warming";
            }
            let metaKeywordDOM = this.getItemToolbar($.mage.__("Meta Keywords:"), this.dataToolbar.metaKeyword, statusMetaKeywords, this.toolTipObject.metaKeyword);

            //For Headings Status
            let headingText = 'H1 [' + this.dataToolbar.headings.h1Elements + '] - H2 [' + this.dataToolbar.headings.h2Elements + '] - H3 [' + this.dataToolbar.headings.h3Elements + '] - H4 [' + this.dataToolbar.headings.h4Elements + '] - H5 [' + this.dataToolbar.headings.h5Elements + ']';
            let statusHeading = "false";
            if (this.dataToolbar.headings.h1Elements === 1 && this.dataToolbar.headings.h2Elements > 0) {
                statusHeading = "success";
            }
            if (this.dataToolbar.headings.h1Elements === 1 && this.dataToolbar.headings.h2Elements === 0) {
                statusHeading = "warming";
            }
            let headingsDOM =  this.getItemToolbar($.mage.__("Headings:"), headingText, statusHeading, this.toolTipObject.headings);

            //For Image status
            let lostAltNumbmer = 0;
            this.dataToolbar.imageObject.each(function(){
                let alt = $(this).attr('alt');
                if (typeof alt === 'undefined' || alt === null || alt === '') {
                    lostAltNumbmer++;
                }
            });
            let imageAltDOM = '';
            let statusImages = "false";
            if (lostAltNumbmer === 0) {
                statusImages = "success";
                imageAltDOM = this.getItemToolbar($.mage.__("All images have ALT attribute."), '', statusImages, statusHeading, this.toolTipObject.images);
            } else {
                let textImageAltDOM = '';
                if (lostAltNumbmer === 1) {
                    textImageAltDOM = lostAltNumbmer + $.mage.__(" image without ALT.");
                } else {
                    textImageAltDOM = lostAltNumbmer + $.mage.__(" images without ALT.");
                }
                imageAltDOM = this.getItemToolbar(textImageAltDOM, '', statusImages, this.toolTipObject.images);
            }

            //For Open Graph
            let countOpenGraph = 0;
            if (typeof this.dataToolbar.openGraph.ogTitle !== "undefined" && this.dataToolbar.openGraph.ogTitle !== '' && this.dataToolbar.openGraph.ogTitle !== null) {
                countOpenGraph++;
            }
            if (typeof this.dataToolbar.openGraph.ogDescription !== "undefined" && this.dataToolbar.openGraph.ogDescription !== '' && this.dataToolbar.openGraph.ogDescription !== null) {
                countOpenGraph++;
            }
            if (typeof this.dataToolbar.openGraph.ogImage !== "undefined" && this.dataToolbar.openGraph.ogImage !== '' && this.dataToolbar.openGraph.ogImage !== null) {
                countOpenGraph++;
            }
            if (typeof this.dataToolbar.openGraph.ogUrl !== "undefined" && this.dataToolbar.openGraph.ogUrl !== '' && this.dataToolbar.openGraph.ogUrl !== null) {
                countOpenGraph++;
            }
            if (typeof this.dataToolbar.openGraph.ogType !== "undefined" && this.dataToolbar.openGraph.ogType !== '' && this.dataToolbar.openGraph.ogType !== null) {
                countOpenGraph++;
            }

            let textOpenGraph = '';
            let statusOpenGraphRender = "false";
            if (countOpenGraph > 0) {
                textOpenGraph = $.mage.__("Great! We have found several Open Graph objects.");
                statusOpenGraphRender = "success";
            } else {
                textOpenGraph = $.mage.__("Your page does not have any Open Graph objects.");
            }

            let openGraphDOM = this.getItemToolbar($.mage.__("Open Graph:"), textOpenGraph, statusOpenGraphRender, this.toolTipObject.openGraph);

            //For Twitter Card
            let countTwitterCard = 0;
            if (typeof this.dataToolbar.twitterCard.twitterSite !== "undefined" && this.dataToolbar.twitterCard.twitterSite !== '' && this.dataToolbar.twitterCard.twitterSite !== null) {
                countTwitterCard++;
            }
            if (typeof this.dataToolbar.twitterCard.twitterTitle !== "undefined" && this.dataToolbar.twitterCard.twitterTitle !== '' && this.dataToolbar.twitterCard.twitterTitle !== null) {
                countTwitterCard++;
            }
            if (typeof this.dataToolbar.twitterCard.twitterDescription !== "undefined" && this.dataToolbar.twitterCard.twitterDescription !== '' && this.dataToolbar.twitterCard.twitterDescription !== null) {
                countTwitterCard++;
            }
            if (typeof this.dataToolbar.twitterCard.twitterImage !== "undefined" && this.dataToolbar.twitterCard.twitterImage !== '' && this.dataToolbar.twitterCard.twitterImage !== null) {
                countTwitterCard++;
            }
            let textTwitterCard = '';
            let statusTwitterCardRender = "false";
            if (countTwitterCard > 0) {
                textTwitterCard = $.mage.__("Nice! We have found Twitter Cards on your page.");
                statusTwitterCardRender = "success";
            } else {
                textTwitterCard = $.mage.__("Your page does not have any Twitter Cards.");
            }

            let twitterCardDOM = this.getItemToolbar($.mage.__("Twitter Card:"), textTwitterCard, statusTwitterCardRender, this.toolTipObject.twitterCard);

            let editLink = this.urlEdit;
            let editBody = '';
            if (editLink) {
                editBody = '<div class="edit_body_toolbar"><a href="' + editLink + '" target="_blank">Edit In Backend</a></div>';
            }
            return firstBodyToolbar + urlDOM + canonicalTagDOM + titleDOM + metaDescriptionDOM + metaKeywordDOM + headingsDOM + imageAltDOM +  openGraphDOM + twitterCardDOM + editBody + lastBodyToolbar;

        },
        handleToolbarStatus: function() {
            let bssSeoToolbarDOM = $("#bss_seo_toolbar");
            bssSeoToolbarDOM.removeClass("toolbar_hide");
            if (this.statusHide) {
                bssSeoToolbarDOM.addClass("toolbar_hide");
            }
        },
        handleClickToolbar: function() {
            let self = this;
            $(document).on('click', "#bss_seo_toolbar .bss_toolbar_header svg", function() {
                let valueCookieHide = '0';
                if (!self.statusHide) {
                    //Save a Cookie and Hide it
                    valueCookieHide = '1';
                    self.statusHide = true;
                } else {
                    self.statusHide = false;
                }
                let hideCookieName = 'bss_seo_toolbar_hide';
                self.createCookie(hideCookieName, valueCookieHide, 30);
                self.handleToolbarStatus();
                let DOMToolbar = self.getDOMToolbar();
                self.renderToolbar(DOMToolbar);
            });
        },
        getItemToolbar: function(title, value, status, toolTip) {
            let icon = '';
            let self = this;
            switch (status) {
                case 'success':
                    icon = self.iconToolbar.success;
                    break;
                case 'false':
                    icon = self.iconToolbar.false;
                    break;
                case 'warming':
                    icon = self.iconToolbar.warming;
                    break;
            }
            return '<div class="bss_toolbar_item" class="tooltip-toggle">' + icon + '<div class="toolbar_content"> <strong>' + title + '</strong> ' + value + ' </div><span class="tooltip-content">'+toolTip+'</span> </div>';
        },
        renderToolbar: function(DOMToolbar) {
            $("#bss_seo_toolbar").show();
            $("#bss_seo_toolbar").html(DOMToolbar);
        },
        setData: function() {
            this.dataToolbar.url = window.location.href;
            let canonicalTag = $('link[rel=canonical]').attr("href");
            if (typeof canonicalTag !== "undefined") {
                this.dataToolbar.canonicalTag = canonicalTag;
            }

            let title = $(document).attr('title');
            if (typeof title !== "undefined") {
                this.dataToolbar.title = title;
            }

            let metaTitle = $('meta[name=title]').attr("content");
            if (typeof metaTitle !== "undefined") {
                this.dataToolbar.metaTitle = metaTitle;
            }

            let metaDescription = $('meta[name=description]').attr("content");
            if  (typeof metaDescription !== "undefined") {
                this.dataToolbar.metaDescription = metaDescription;
            }

            let metaKeyword = $('meta[name=keywords]').attr("content");
            if (typeof metaKeyword !== "undefined") {
                this.dataToolbar.metaKeyword = metaKeyword;
            }
            this.dataToolbar.headings.h1Elements = $("h1").length;
            this.dataToolbar.headings.h2Elements = $("h2").length;
            this.dataToolbar.headings.h3Elements = $("h3").length;
            this.dataToolbar.headings.h4Elements = $("h4").length;
            this.dataToolbar.headings.h5Elements = $("h5").length;
            this.dataToolbar.imageObject = $("img");

            this.dataToolbar.openGraph.ogTitle = $('meta[property="og:title"]').attr("content");
            this.dataToolbar.openGraph.ogImage = $('meta[property="og:image"]').attr("content");
            this.dataToolbar.openGraph.ogDescription = $('meta[property="og:description"]').attr("content");
            this.dataToolbar.openGraph.ogUrl = $('meta[property="og:url"]').attr("content");
            this.dataToolbar.openGraph.ogType = $('meta[property="og:type"]').attr("content");

            this.dataToolbar.twitterCard.twitterSite =  $('meta[name="twitter:site"]').attr("content");
            this.dataToolbar.twitterCard.twitterTitle =  $('meta[name="twitter:title"]').attr("content");
            this.dataToolbar.twitterCard.twitterDescription =  $('meta[name="twitter:description"]').attr("content");
            this.dataToolbar.twitterCard.twitterImage =  $('meta[name="twitter:image"]').attr("content");
        },
        getUrlVars: function()
        {
            let vars = [], hash;
            let hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for(let i = 0; i < hashes.length; i++)
            {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        },
        createCookie: function(name, value, days) {
            let expires;
            if (days) {
                let date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toGMTString();
            }
            else {
                expires = "";
            }

            document.cookie = name + "=" + value + expires + "; path=/";
        },
        getCookie: function(cookie_name) {
            let cookie_value = "";
            if (document.cookie.length > 0) {
                let c_start = document.cookie.indexOf(cookie_name + "=");
                if (c_start !== -1) {
                    c_start = c_start + cookie_name.length + 1;
                    let c_end = document.cookie.indexOf(";", c_start);
                    if (c_end === -1) {
                        c_end = document.cookie.length;
                    }
                    cookie_value = unescape(document.cookie.substring(c_start, c_end));
                }
            }
            return cookie_value;
        },
        iconToolbar: {
            success: '<svg viewBox="0 0 512 512"><path fill="#28a745" d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"></path></svg>',
            false: ' <svg viewBox="0 0 512 512" class="tooltip-toggle"><path fill="#dc3545" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zm-248 50c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"></path></svg>',
            warming: ' <svg viewBox="0 0 512 512" class="tooltip-toggle"><path fill="#aaa" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path></svg>'
        },
        toolTipObject: {
            url: 'A SEO-friendly URL should be easy-to-read, definitive, and keyword-rich so that Google can easily index your website.',
            headings: 'You only use one &lt;H1&gt; heading for each page to strengthen your SEO.<br/>This &lt;H1&gt; heading should contain your main keyword.',
            images: 'You should add ALT text to your images to help search engines easily index them.<br/>The reason is search engines don\'t physically see images like the way people do. Hence, ALT text plays a role in describing the image so that they show up in Google™ and other search engine\'s image results.<br/>Make sure your website images have their own specific ALT text.',
            openGraph: 'Open Graph is a technology introduced by Facebook that integrates between Facebook and its user data and a website. When integrating Open Graph meta tags into your page, you can identify which elements of your page you desire to display when your page is shared.',
            twitterCard: 'By using Twitter Cards, you can easily attach photo, videos, and media experience to Tweets to increase traffic to your site.',
            canonicalTag: 'A canonical tag is used to prevent issues caused by duplicated content appearing on multiple URLs. On the other hand, the canonical tag lets search engines which version of URL will be included in search results.',
            title: 'A perfect length of a title should be about 10-70 characters, including spaces. You need to make your title concise and contain the main keyword.',
            description: 'Meta description determines how your pages are displayed in the search results. Hence, you should keep it 200-255 characters long for the best effectiveness. Also, don\'t forget to include your main keyword in the meta description and make it attractive to encourage customers to click.',
            metaKeyword: 'Meta keywords are words or phrases that pertain to your site\'s content. In the past, people have tried to take advantage of this tag so now it doesn\'t affect your search rankings the way that it used to.'
        }
    });
    return $.bss.seo_toolbar;
});
