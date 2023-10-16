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
 * @package     Mageplaza_PaymentRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PaymentRestriction\Block\Adminhtml\Rule\Edit\Tab\Renderer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Mageplaza\PaymentRestriction\Helper\Data as HelperData;

/**
 * Class PaymentMethod
 * @package Mageplaza\PaymentRestriction\Block\Adminhtml\Rule\Edit\Tab\Renderer
 */
class PaymentMethod extends AbstractElement
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @type ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * PaymentMethod constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Registry $coreRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Registry $coreRegistry,
        ScopeConfigInterface $scopeConfig,
        HelperData $helperData,
        $data = []
    ) {
        $this->_scopeConfig  = $scopeConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_helperData   = $helperData;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('multiselect');
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getElementHtml()
    {
        $html               = '';
        $rule               = $this->_coreRegistry->registry('mageplaza_paymentrestriction_rule');
        $rulePaymentMethods = explode(',', $rule->getPaymentMethods());
        $html               .= '<div id="payment_methods_select_container">';
        $html               .= '<select name="rule[payment_methods][]" size="10" multiple="multiple"  class="required-entry _required select multiselect admin__control-multiselect">';
        foreach ($this->_helperData->getActiveMethods() as $paymentMethodGroupTitle => $paymentMethodGroup) {
            if (is_array($paymentMethodGroup)) {
                $html .= '<optgroup label="' . ucwords($paymentMethodGroupTitle) . '">';
                foreach ($paymentMethodGroup as $paymentCode) {
                    $paymentTitle = $this->_helperData
                        ->getConfigValue('payment/' . $paymentCode . '/title');
                    if ($paymentTitle != '') {
                        $html .= '<option value="' . $paymentCode . '"';
                        $html .= (in_array($paymentCode, $rulePaymentMethods)) ? ' selected>' : '>';
                        $html .= $paymentTitle;
                        $html .= '</option>';
                    }
                }
                $html .= '</optgroup>';
            } else {
                $paymentTitle = $this->_helperData
                    ->getConfigValue('payment/' . $paymentMethodGroup . '/title');
                if ($paymentTitle != '') {
                    $html .= '<option value="' . $paymentMethodGroup . '"';
                    $html .= (in_array($paymentMethodGroup, $rulePaymentMethods)) ? ' selected>' : '>';
                    $html .= $paymentTitle;
                    $html .= '</option>';
                }
            }
        }
        $html .= '</select>';
        $html .= '</div>';
        $html .= '<div id="mp-select-all-container"><input id="mp-select-all" type="checkbox" value="select_all_methods" />';
        $html .= '<label for="mp-select-all">' . __('Select All') . '</label></div>';

        return $html;
    }
}
