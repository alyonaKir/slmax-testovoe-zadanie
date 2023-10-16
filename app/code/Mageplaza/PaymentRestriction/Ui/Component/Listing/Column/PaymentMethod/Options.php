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

namespace Mageplaza\PaymentRestriction\Ui\Component\Listing\Column\PaymentMethod;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Mageplaza\PaymentRestriction\Helper\Data as HelperData;
use Mageplaza\PaymentRestriction\Model\Rule;
use Mageplaza\PaymentRestriction\Model\RuleFactory;

/**
 * Class Options
 * @package Mageplaza\PaymentRestriction\Ui\Component\Listing\Column\PaymentMethod
 */
class Options implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RuleFactory
     */
    protected $_paymentRuleFactory;

    /**
     * Options constructor.
     *
     * @param Escaper $escaper
     * @param HelperData $helperData
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Escaper $escaper,
        HelperData $helperData,
        RuleFactory $ruleFactory
    ) {
        $this->escaper             = $escaper;
        $this->_helperData         = $helperData;
        $this->_paymentRuleFactory = $ruleFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array_values($this->generateCurrentOptions());
    }

    /**
     * @return array
     */
    protected function generateCurrentOptions()
    {
        $options = [];
        /** @var Rule $paymentRule */
        $paymentRule               = $this->_paymentRuleFactory->create();
        $pickedPaymentMethods      = $paymentRule->getResource()->getPaymentMethods();
        $pickedPaymentMethods      = implode(',', $pickedPaymentMethods);
        $pickedPaymentMethodsArray = explode(',', $pickedPaymentMethods);
        $pickedPaymentMethodsArray = array_unique($pickedPaymentMethodsArray);

        $methodCollection = $this->_helperData->getActiveMethods();
        foreach ($methodCollection as $paymentMethodGroupTitle => $paymentMethodGroup) {
            $isExist = false;
            if (is_array($paymentMethodGroup)) {
                foreach ($paymentMethodGroup as $paymentCode) {
                    if (in_array($paymentCode, $pickedPaymentMethodsArray)) {
                        $isExist = true;
                        break;
                    }
                }
                if ($isExist) {
                    $name                    = $this->escaper->escapeHtml(ucwords($paymentMethodGroupTitle));
                    $options[$name]['label'] = $name;
                    foreach ($paymentMethodGroup as $paymentCode) {
                        if (in_array($paymentCode, $pickedPaymentMethodsArray)) {
                            $paymentTitle              = $this->_helperData
                                ->getConfigValue('payment/' . $paymentCode . '/title');
                            $item['value']             = $paymentCode;
                            $item['label']             = str_repeat(' ', 4) . $this->escaper->escapeHtml($paymentTitle);
                            $options[$name]['value'][] = $item;
                        }
                    }
                }
            } elseif (in_array($paymentMethodGroup, $pickedPaymentMethodsArray)) {
                $paymentTitle  = $this->_helperData->getConfigValue('payment/' . $paymentMethodGroup . '/title');
                $item['value'] = $paymentMethodGroup;
                $item['label'] = $this->escaper->escapeHtml($paymentTitle);
                $options[]     = $item;
            }
        }

        return $options;
    }
}
