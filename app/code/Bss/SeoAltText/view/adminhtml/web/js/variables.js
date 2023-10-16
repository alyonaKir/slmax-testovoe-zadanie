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
    'jquery'
], function ($) {
    'use strict';
    $.widget('bss.insert_button_alt_text', {
        idFrame: "",
        checkedArray: [],
        _create: function () {
            this.processInsert();
        },
        processInsert: function() {
            let self = this;
            //Click Insert
            $(document).on('click', "button#bss_insert_button", function(ev) {
                self.openVariablesContainer();
                self.idFrame = '#bss_seo_alt_text_general_file_template';
            });

            //Click Insert
            $(document).on('click', "button#bss_insert_button_second", function(ev) {
                self.openVariablesContainer();
                self.idFrame = '#bss_seo_alt_text_general_alt_template';
            });

            //Close Button
            $(document).on("click", "#bss-overlay,#bss-close", function (ev) {
                self.closeVariablesContainer();
            });

            $(document).on("click", ".bss-insert-checked-variables", function (ev) {
                $("input:checkbox[name=attibute-value]:checked").each(function () {
                    self.checkedArray.push($(this).val());
                });

                let checkedValue = self.checkedArray.join(" ");
                let frameInput = $(self.idFrame);
                let currentValue = frameInput.val();
                let newValue = '';
                if (currentValue) {
                    newValue = currentValue + ' ' + checkedValue;
                } else {
                    newValue = checkedValue;
                }

                frameInput.val(newValue);
                $('input:checkbox').removeAttr('checked');
                self.checkedArray = [];
                self.closeVariablesContainer();
            });

            //Insert 1 variable by link click
            $(document).on("click", ".variable", function (e) {
                e.preventDefault();
                let value = $(this).data('insert');
                let frameInput = $(self.idFrame);
                let currentValue = frameInput.val();
                let newValue = '';
                if (currentValue) {
                    newValue = currentValue + ' ' + value;
                } else {
                    newValue = value;
                }
                frameInput.val(newValue);
                self.closeVariablesContainer();
            })
        },
        closeVariablesContainer: function() {
            $(".bss-overlay").hide();
            $(".bss-variables-container").hide("slide",{ direction: "right" },200);
            $(".textarea-active").removeClass("textarea-active");
        },
        openVariablesContainer: function() {
            $(".bss-overlay").show();
            $(".bss-variables-container").show("slide",{ direction: "right" },200);
        },
        insertAtCaret: function (myValue) {
            return this.each(function (i) {
                if (document.selection) {
                    //For browsers like Internet Explorer
                    this.focus();
                    let sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else if (this.selectionStart || this.selectionStart === '0') {
                    //For browsers like Firefox and Webkit based
                    let startPos = this.selectionStart;
                    let endPos = this.selectionEnd;
                    let scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            })
        }
    });
    return $.bss.insert_button_alt_text;
});
