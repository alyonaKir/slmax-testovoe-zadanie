/*
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 *  @category  BSS
 *  @package   Bss_ProductLabel
 *  @author    Extension Team
 *  @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define(['jquery', 'domReady!'], function (jQuery) {
    // this function will be called after 'contentUpdated' event (like AJAX action)
    jQuery('body').on('contentUpdated', function() {
        setLabel();
    });

    // this function will be called after any AJAX request
    jQuery(document).ajaxSuccess(function() {
        setLabel();
    });

    // set label image size/position
    function setLabel() {
        jQuery('.label-image').each(function () {
            jQuery(this).css('position', 'absolute');
            jQuery(this).css('background-size', 'contain');
            jQuery(this).css('background-repeat', 'no-repeat');
            jQuery(this).css('background-position', 'center');

            jQuery(this).css('display', jQuery(this).attr('data-display'));
            jQuery(this).css('background-image', 'url(' + jQuery(this).attr('data-background-image') + ')');

            var imgWrapper = jQuery(this).siblings('.product-image-container');
            if (imgWrapper.length) {
                var height = parseFloat(jQuery(this).attr('data-height')) * imgWrapper.width() / imgWrapper.height() + '%';
                var top = parseFloat(jQuery(this).attr('data-top')) + (imgWrapper.width() / imgWrapper.height()) + '%';
            } else {
                var height = jQuery(this).attr('data-height');
                var top = jQuery(this).attr('data-top');
            }
            jQuery(this).css('height', height);
            jQuery(this).css('top', top);

            jQuery(this).css('width', jQuery(this).attr('data-width'));
            jQuery(this).css('left', jQuery(this).attr('data-left'));
            jQuery(this).css('z-index', jQuery(this).attr('data-priority'));
        })
    }

    return setLabel();
});
