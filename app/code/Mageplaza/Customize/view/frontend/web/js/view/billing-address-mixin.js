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
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';
    return function (BillingAddressComponent) {
        return BillingAddressComponent.extend({
            afterResolveDocument: function () {
                this._super();
                if (!quote.isVirtual()) {
                    $('#billing-address-same-as-shipping').trigger('click');
                    this.isAddressSameAsShipping(true);
                }
            }
        })
    }
});
