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
 * @package     Mageplaza_ShippingRestricion
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
	'jquery',
	'Magento_Checkout/js/model/quote',
	'Magento_Checkout/js/model/shipping-service',
	'rjsResolver'
], function (
	$,
	quote,
	shippingService,
	resolver
) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function() {
            	this._super();

            	var shippingRate = shippingService.getShippingRates();

                shippingRate.subscribe(function(value){

                    if(quote.shippingMethod()) {
                        let quoteShippingMethod = quote.shippingMethod(),
                            isApply = false;

                        $.each(shippingRate(), function(key, shippingMethod){
                            if (quoteShippingMethod.method_code == shippingMethod.method_code) {
                                isApply = true;
                            }
                        });

                        if (!isApply) {
                            quote.shippingMethod(shippingRate.length ? shippingRate[0] : null);
                        }
                    }

                });

            	resolver(this.afterResolveDocument.bind(this));

            },

            afterResolveDocument: function() {
            	var shippingRate = shippingService.getShippingRates()();

                if(quote.shippingMethod()) {
                    let quoteShippingMethod = quote.shippingMethod(),
                        isApply = false;

                    $.each(shippingRate, function(key, shippingMethod){
                        if (quoteShippingMethod.method_code == shippingMethod.method_code) {
                            isApply = true;
                        }
                    });

                    if (!isApply) {
                        quote.shippingMethod(shippingRate.length ? shippingRate[0] : null);
                    }
                }
            }
        });
    };
});
