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

namespace Mageplaza\ShippingRestriction\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as SaleRuleColFact;

/**
 * Class SaleRule
 * @package Mageplaza\ShippingRestriction\Model\Config\Source
 */
class SaleRule implements ArrayInterface
{
    /**
     * @var SaleRuleColFact
     */
    protected $_saleRuleColFact;

    /**
     * SaleRule constructor.
     *
     * @param SaleRuleColFact $saleRuleColFact
     */
    public function __construct(SaleRuleColFact $saleRuleColFact)
    {
        $this->_saleRuleColFact = $saleRuleColFact;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Collection $saleRuleCollection */
        $saleRuleCollection = $this->_saleRuleColFact->create()->addFieldToFilter('is_active', 1);
        $options[]          = ['value' => '0', 'label' => '-- Please Select --'];
        foreach ($saleRuleCollection as $rule) {
            $options[] = [
                'value' => $rule->getId(),
                'label' => $rule->getName()
            ];
        }

        return $options;
    }
}
