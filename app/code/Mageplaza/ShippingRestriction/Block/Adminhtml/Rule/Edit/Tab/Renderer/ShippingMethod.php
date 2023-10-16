<?php
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
 * @package     Mageplaza_ShippingRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRestriction\Block\Adminhtml\Rule\Edit\Tab\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Mageplaza\ShippingRestriction\Helper\Data as HelperData;

/**
 * Class ShippingMethod
 * @package Mageplaza\ShippingRestriction\Block\Adminhtml\Rule\Edit\Tab\Renderer
 */
class ShippingMethod extends AbstractElement
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * ShippingMethod constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Registry $coreRegistry
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Registry $coreRegistry,
        HelperData $helperData,
        $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_helperData   = $helperData;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('multiselect');
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $carriers            = $this->_helperData->getShippingMethods();
        $rule                = $this->_coreRegistry->registry('mageplaza_shippingrestriction_rule');
        $ruleShippingMethods = explode(',', $rule->getShippingMethods());
        $html                = '<div id="shipping_methods_select_container">';
        $html                .= '<select name="rule[shipping_methods][]" size="10" multiple="multiple" class="select multiselect admin__control-multiselect">';
        foreach ($carriers as $carrier) {
            $html .= '<optgroup label="' . $carrier['label'] . '">';
            /** @var array[] $child */
            foreach ($carrier['value'] as $child) {
                if ($child['label']) {
                    $html .= '<option value="' . $child['value'] . '"';
                    $html .= in_array($child['value'], $ruleShippingMethods, true) ? ' selected>' : '>';
                    $html .= $child['label'];
                    $html .= '</option>';
                }
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';
        $html .= '</div>';
        $html .= '<div id="mp-select-all-container"><input id="mp-select-all" type="checkbox" value="select_all_methods" />';
        $html .= '<label for="mp-select-all">' . __('Select All') . '</label></div>';

        return $html;
    }
}
