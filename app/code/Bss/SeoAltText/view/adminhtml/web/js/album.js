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
    'vue',
    'mage/translate'
], function ($, Vue) {
    "use strict";
    $.widget('bss.seo_alt_text', {
        _create: function () {
            let maegntoJs = this;
            new Vue({
                el: '#bss_alt_text_album',
                data: {
                    options: maegntoJs.options,
                    galleryPage: 1,
                    galleryPerPage: 18,
                    galleryObject: [],
                    galleryLoading: false,
                    galleryLoadMore: false,
                    galleryFirstLoad: true,
                    statusOpenImage: false,
                    imageOpenObject: {},
                    itemOpenObject: {},
                    indexOpen: '',
                    indexOpenObject: {
                        indexItem: '',
                        indexImage: '',
                    },
                    altEditText: '',
                    fileEditText: '',
                    statusNotification: '',
                    messageNotification: '',
                    statusButtonLoading: false,
                    statusButtonDisable: false,
                    setTimeoutCheck: '',
                    cookieName: 'bss_seo_alt_text_filters',
                    dataFilters: {
                        store: '0',
                        name: '',
                        visibility: '',
                        status: '',
                        attributeSet: '',
                        type: '',
                        sku: ''
                    }
                },
                created: function () {
                    let cookieValue = this.getCookie(this.cookieName);
                    let self = this;
                    if (cookieValue !== '' && cookieValue !== null) {
                        try {
                            this.dataFilters = JSON.parse(cookieValue);
                        } catch (error) {
                            self.dataFilters = {
                                store: '0',
                                name: '',
                                visibility: '',
                                status: '',
                                attributeSet: '',
                                type: '',
                                sku: ''
                            };
                        }
                    }
                    this.getGalleriesData();
                },
                methods: {
                    getGalleriesData() {
                        let self = this;
                        let url = this.options.galleriesLink;
                        this.galleryLoading = true;
                        this.galleryLoadMore = false;
                        if (self.processActiveFilters()) {
                            let dataToCookie = JSON.stringify(this.dataFilters);
                            this.createCookie(self.cookieName, dataToCookie, 30);
                        } else {
                            this.removeCookie(self.cookieName);
                        }
                        $.ajax({
                            showLoader: false,
                            url: url,
                            data : {
                                page: this.galleryPage,
                                per_page: this.galleryPerPage,
                                filters: this.dataFilters
                            },
                            type: "POST",
                            dataType: 'json',
                            complete: function(response) {
                                self.galleryFirstLoad = false;
                                self.galleryLoading = false;
                                let result = response.responseText;
                                try {
                                    result = JSON.parse(result);
                                    self.galleryObject = self.galleryObject.concat(result.data);
                                    self.galleryLoadMore = result.data.length >= self.galleryPerPage;
                                } catch (err) {
                                    console.log(err);
                                }
                            },
                            error: function(error) {
                                self.galleryFirstLoad = false;
                                self.galleryLoading = false;
                            }
                        });
                    },
                    handleLoadMoreGallery() {
                        this.galleryPage++;
                        this.getGalleriesData();
                    },
                    processResetData() {
                        this.galleryObject = [];
                        this.galleryPage = 1;
                        this.galleryFirstLoad = true;
                        this.statusOpenImage = false;
                        this.imageOpenObject = {};
                        this.itemOpenObject = {};
                        this.indexOpen = '';
                        this.indexOpenObject = {
                            indexItem: '',
                            indexImage: '',
                        };
                        this.altEditText = '';
                        this.fileEditText = '';
                        this.statusNotification = '';
                        this.messageNotification = '';
                    },
                    handleChangeFilters() {
                        this.processResetData();
                        this.getGalleriesData();
                    },
                    handleOpenImage(index, itemIndex) {
                        this.statusOpenImage = true;
                        this.imageOpenObject = this.galleryObject[index].images[itemIndex];
                        this.itemOpenObject = this.galleryObject[index];
                        this.indexOpen = index + '_' + itemIndex;
                        this.indexOpenObject.indexImage = index;
                        this.indexOpenObject.indexItem = itemIndex;
                        this.altEditText = this.galleryObject[index].images[itemIndex].alt;
                        this.fileEditText = this.galleryObject[index].images[itemIndex].file;
                        this.handleKeyupInput();
                    },
                    handleCloseImage() {
                        this.statusOpenImage = false;
                        this.imageOpenObject = {};
                        this.itemOpenObject = {};
                        this.indexOpen = '';
                        this.indexOpenObject = {
                            indexItem: '',
                            indexImage: '',
                        };
                        this.altEditText = '';
                        this.fileEditText = '';
                    },
                    handleOpenAlbum(index) {
                        this.galleryObject[index].isOpen = true;
                        this.reRenderObject();
                    },
                    handleCloseAlbum(index) {
                        this.galleryObject[index].isOpen = false;
                        this.reRenderObject();
                    },
                    reRenderObject() {
                        let galleryBackup = this.galleryObject;
                        this.galleryObject = [];
                        try {
                            let galleryJson = JSON.stringify(galleryBackup);
                            this.galleryObject =  JSON.parse(galleryJson);
                        } catch (e) {
                            this.galleryObject = galleryBackup;
                        }
                    },
                    validateFileName(fileName) {
                        let validFilename = /^[a-z0-9_.@()-]+\.[^.]+$/i.test(fileName);
                        let extensionFile = fileName.split('.').pop().toLowerCase();
                        let allowedExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
                        let validExtension = allowedExtension.includes(extensionFile);
                        return validFilename && validExtension;
                    },
                    handleSaveConfig() {
                        let fileName = this.fileEditText;
                        let validFilename = this.validateFileName(fileName);
                        if (!validFilename) {
                            this.statusNotification = 'error';
                            this.messageNotification = $.mage.__('Filename is not valid.');
                            return false;
                        }
                        let self = this;
                        this.processSaveConfig();
                    },
                    processSaveConfig() {
                        if (this.statusButtonLoading) {
                            return false;
                        }
                        this.statusButtonDisable = true;
                        this.statusButtonLoading = true;
                        let url = this.options.productLink;
                        let self = this;
                        $.ajax({
                            showLoader: false,
                            url: url,
                            data : {
                                product_id: this.itemOpenObject.product_id,
                                value_id: this.imageOpenObject.value_id,
                                label: this.altEditText,
                                file_name: this.fileEditText,
                                store: this.dataFilters.store
                            },
                            type: "POST",
                            dataType: 'json',
                            complete: function(response) {
                                self.statusButtonDisable = false;
                                self.statusButtonLoading = false;
                                let result = response.responseText;
                                try {
                                    result = JSON.parse(result);
                                    if (result.status) {
                                        self.statusNotification = 'success';
                                        if (typeof result.data.image_name !== "undefined") {
                                            self.fileEditText = result.data.image_name;
                                        }
                                        self.galleryObject[self.indexOpenObject.indexImage].images[self.indexOpenObject.indexItem].alt = self.altEditText;
                                        self.galleryObject[self.indexOpenObject.indexImage].images[self.indexOpenObject.indexItem].file = self.fileEditText;

                                        if (typeof result.data.image_url !== "undefined") {
                                            self.galleryObject[self.indexOpenObject.indexImage].images[self.indexOpenObject.indexItem].url = result.data.image_url;
                                        }
                                    } else {
                                        self.statusNotification = 'error';
                                    }
                                    self.messageNotification = result.message;
                                } catch (error) {
                                    self.statusNotification = 'error';
                                    self.messageNotification = error;
                                }
                            },
                            error: function(error) {
                                self.statusButtonDisable = false;
                                self.statusButtonLoading = false;
                                self.statusNotification = 'error';
                                self.messageNotification = error;
                            }
                        });
                    },
                    handleKeyupInput() {
                        this.statusNotification = '';
                        this.messageNotification = '';
                    },
                    getFiltersValue(type) {
                        let labelReturn = '';
                        let self = this;
                        switch (type) {
                            case 'store':
                                labelReturn = self.getStoreLabel();
                                break;
                            case 'status':
                                labelReturn = self.options.statusArray[self.dataFilters.status];
                                break;
                            case 'visibility':
                                labelReturn = self.options.visibilityArray[self.dataFilters.visibility];
                                break;
                            case 'attribute_set':
                                labelReturn = self.options.attributeSetArray[self.dataFilters.attributeSet];
                                break;
                            case 'type':
                                labelReturn = self.options.typeArray[self.dataFilters.type];
                                break;
                            case 'name':
                                labelReturn = self.dataFilters.name;
                                break;
                            case 'sku':
                                labelReturn = self.dataFilters.sku;
                                break;
                        }
                        return labelReturn;
                    },
                    handleRemoveFilters(type) {
                        let self = this;
                        switch (type) {
                            case 'store':
                                self.dataFilters.store = '0';
                                break;
                            case 'status':
                                self.dataFilters.status = '';
                                break;
                            case 'visibility':
                                self.dataFilters.visibility = '';
                                break;
                            case 'attribute_set':
                                self.dataFilters.attributeSet = '';
                                break;
                            case 'type':
                                self.dataFilters.type = '';
                                break;
                            case 'name':
                                self.dataFilters.name = '';
                                break;
                            case 'sku':
                                self.dataFilters.sku = '';
                                break;
                        }
                        this.handleChangeFilters();
                    },
                    getStoreLabel() {
                        let self = this;
                        let labelReturn = '';
                        for (let index in this.options.storeArray) {
                            let value = self.options.storeArray[index].value;
                            if (Number(value) === Number(self.dataFilters.store)) {
                                labelReturn = self.options.storeArray[index].label;
                            }
                        }
                        return labelReturn;
                    },
                    handleRemoveAllFilters() {
                        let self = this;
                        self.dataFilters.store = '0';
                        self.dataFilters.status = '';
                        self.dataFilters.visibility = '';
                        self.dataFilters.attributeSet = '';
                        self.dataFilters.type = '';
                        self.dataFilters.name = '';
                        self.dataFilters.sku = '';
                        self.handleChangeFilters();
                    },
                    processKeyupChange() {
                        let self = this;
                        clearTimeout(self.setTimeoutCheck);
                        self.setTimeoutCheck = setTimeout(function () {
                            self.handleChangeFilters();
                        }, 500);
                    },
                    processActiveFilters() {
                        return (Number(this.dataFilters.store) !== 0 || (this.dataFilters.name !== '' && this.dataFilters.name !== null) || this.dataFilters.visibility !== '' || this.dataFilters.attributeSet !== ''|| this.dataFilters.status !== '' || this.dataFilters.type !== '' || (this.dataFilters.sku !== '' && this.dataFilters.sku !== null));
                    },
                    createCookie(name, value, days) {
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
                    getCookie(cookie_name) {
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
                    removeCookie(name) {
                        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
                    }
                }
            });
        }
    });
    return $.bss.seo_alt_text;
});