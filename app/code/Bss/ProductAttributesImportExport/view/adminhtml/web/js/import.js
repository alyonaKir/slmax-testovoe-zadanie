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
 * @package    Bss_ProductAttributesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
require([
    'jquery'
], function ($) {
    $('#entity').change(function () {
        if ($('#entity').val()=='product_attributes') {
            $('#basic_behavior_import_multiple_value_separator').val('|');
            $('.field-basic_behaviorfields_enclosure').hide();
            $('.field-basic_behavior__import_field_separator').hide();
            $('.field-basic_behavior_import_empty_attribute_value_constant').hide();

            var newField = $('#basic_behavior__import_field_separator').parent().parent().clone();
            var optionFieldSeparator = newField.find('#basic_behavior__import_field_separator');
            optionFieldSeparator.attr('id', 'basic_behavior_import_option_value_separator');
            optionFieldSeparator.attr('name', '_import_option_value_separator');
            optionFieldSeparator.val(':');
            newField.find('label span').html('Option Value Separator');
            newField.show();
            $('#basic_behavior_fieldset').append(newField);

            var newField1 = $('#basic_behavior__import_field_separator').parent().parent().clone();
            var storeViewSeperator = newField1.find('#basic_behavior__import_field_separator');
            storeViewSeperator.attr('id', 'basic_behavior_import_store_view_separator');
            storeViewSeperator.attr('name', '_import_store_view_separator');
            storeViewSeperator.val(';');
            newField1.find('label span').html('Store View Separator');
            newField1.show();
            $('#basic_behavior_fieldset').append(newField1);

            $('#bss-version').appendTo('.field-entity .admin__field-control.control .admin__field');
            $('#bss-version').show();
        } else {
            $('#basic_behavior_import_multiple_value_separator').val(',');
            $('#bss-version').hide();
        }
    });

})
