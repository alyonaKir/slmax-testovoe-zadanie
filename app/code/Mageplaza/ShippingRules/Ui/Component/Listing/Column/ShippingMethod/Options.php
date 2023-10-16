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
 * @package     Mageplaza_ShippingRules
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRules\Ui\Component\Listing\Column\ShippingMethod;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Mageplaza\ShippingRules\Helper\Data as HelperData;
use Mageplaza\ShippingRules\Model\RuleFactory;

/**
 * Class Options
 * @package Mageplaza\ShippingRules\Ui\Component\Listing\Column\ShippingMethod
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
     * @var \Mageplaza\ShippingRules\Model\RuleFactory
     */
    protected $_shippingRuleFactory;

    /**
     * Options constructor.
     *
     * @param \Magento\Framework\Escaper $escaper
     * @param \Mageplaza\ShippingRules\Helper\Data $helperData
     * @param \Mageplaza\ShippingRules\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        Escaper $escaper,
        HelperData $helperData,
        RuleFactory $ruleFactory
    ) {
        $this->escaper = $escaper;
        $this->_helperData = $helperData;
        $this->_shippingRuleFactory = $ruleFactory;
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
        /** @var \Mageplaza\ShippingRules\Model\Rule $shippingRule */
        $shippingRule = $this->_shippingRuleFactory->create();
        $pickedShippingMethods = $shippingRule->getResource()->getShippingMethods();
        $pickedShippingMethods = implode(',', $pickedShippingMethods);
        $pickedShippingMethodsArray = explode(',', $pickedShippingMethods);
        $pickedShippingMethodsArray = array_unique($pickedShippingMethodsArray);

        $methodCollection = $this->_helperData->getShippingMethods();
        foreach ($methodCollection as $method) {
            $isExist = false;
            foreach ($method['value'] as $item) {
                if (in_array($item['value'], $pickedShippingMethodsArray)) {
                    $isExist = true;
                    break;
                }
            }
            if ($isExist) {
                $name = $this->escaper->escapeHtml($method['label']);
                $options[$name]['label'] = $name;
                foreach ($method['value'] as $item) {
                    if (in_array($item['value'], $pickedShippingMethodsArray)) {
                        $item['label'] = str_repeat(' ', 4) . $this->escaper->escapeHtml($item['label']);
                        $options[$name]['value'][] = $item;
                    }
                }
            }
        }

        return $options;
    }
}
