/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Theme/js/model/breadcrumb-list'
], function ($, breadcrumbList) {
    'use strict';
    return function (widget) {

        $.widget('mage.breadcrumbs', widget, {
            options: {
                categoryUrlSuffix: '',
                useCategoryPathInUrl: false,
                product: '',
                categoryItemSelector: '.category-item',
                menuContainer: '[data-action="navigation"] > ul'
            },

            /** @inheritdoc */
            _render: function () {
            },
        });

        return $.mage.breadcrumbs;
    };
});
