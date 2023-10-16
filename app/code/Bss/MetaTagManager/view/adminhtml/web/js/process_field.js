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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/translate'
], function ($) {
    $.widget('bss.process_field', {
        _create: function () {
            let productType = $("#rule_meta_type");
            let productTypeValue = productType.val();
            this.processShowHide(productTypeValue);
            let self = this;
            $(document).on('change', '#rule_meta_type', function(e) {
                let productTypeValue = productType.val();
                self.processShowHide(productTypeValue);
            });
        },
        processShowHide: function(productTypeValue) {
            let productConditionsBlock = $("#catalog_rule_formrule_conditions_fieldset");
            let categoryBlock = $("#rule_category_conditions_fieldset");
            let variableProductBlock = $("#bss-variables-container .variable-group.product-group");
            let variableCategoryBlock = $("#bss-variables-container .variable.variable-cate_description");
            let variableCategoryInput = $("#bss-variables-container input[value='[cate_description]']");
            let shortDescription = $(".admin__field.field.field-short_description");
            if (productTypeValue === 'category') {
                productConditionsBlock.hide();
                categoryBlock.show();
                variableProductBlock.hide();
                variableCategoryBlock.show();
                variableCategoryInput.show();
                shortDescription.hide();
            } else {
                categoryBlock.hide();
                productConditionsBlock.show();
                variableProductBlock.show();
                variableCategoryBlock.hide();
                variableCategoryInput.hide();
                shortDescription.show();
            }
        }
    });
    return $.bss.process_field;
});
