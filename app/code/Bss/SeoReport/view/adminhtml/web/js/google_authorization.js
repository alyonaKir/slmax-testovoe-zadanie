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
    'jquery'
], function ($) {
    'use strict';
    $.widget('bss.report_field', {
        _create: function () {
            let options = this.options;
            let self = this;
            $('button#google_authorization').click(function() {
                let width = window.innerWidth * 0.66 ;
                let url = 'https://accounts.google.com/o/oauth2/auth?response_type=code&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&client_id=349600568090-lkd6thrghmt050h47ajdedtu5898ejvd.apps.googleusercontent.com&scope=https://www.googleapis.com/auth/webmasters+https://www.googleapis.com/auth/webmasters.readonly&access_type=offline&approval_prompt=force';
                let height = width * window.innerHeight / window.innerWidth ;
                window.open(url , 'newwindow', 'width=' + width + ', height=' + height + ', top=' + ((window.innerHeight - height) / 2) + ', left=' + ((window.innerWidth - width) / 2));
            });
        }
    });
    return $.bss.report_field;
});
