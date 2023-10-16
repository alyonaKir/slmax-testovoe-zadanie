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

namespace Mageplaza\ShippingRules\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\SalesRule\Model\RuleFactory as SaleRuleFactory;

/**
 * Class SaleRule
 * @package Mageplaza\ShippingRules\Model\Config\Source
 */
class SaleRule implements ArrayInterface
{
    /**
     * @var SaleRuleFactory
     */
    protected $_saleRuleFactory;

    /**
     * SaleRule constructor.
     *
     * @param SaleRuleFactory $saleRuleFactory
     */
    public function __construct(SaleRuleFactory $saleRuleFactory)
    {
        $this->_saleRuleFactory = $saleRuleFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $saleRule = $this->_saleRuleFactory->create();
        $saleRuleCollection = $saleRule->getCollection()->addFieldToFilter('is_active', 1);
        $options[] = ['value' => '0', 'label' => '-- Please Select --'];
        foreach ($saleRuleCollection as $rule) {
            $options[] = [
                'value' => $rule->getId(),
                'label' => $rule->getName()
            ];
        }

        return $options;
    }
}
