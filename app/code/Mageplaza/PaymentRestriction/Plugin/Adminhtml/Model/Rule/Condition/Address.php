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

namespace Mageplaza\PaymentRestriction\Plugin\Adminhtml\Model\Rule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Address as ConditionAddress;
use Mageplaza\PaymentRestriction\Plugin\PaymentRestrictionPlugin;

/**
 * Class Address
 * @package Mageplaza\PaymentRestriction\Plugin\Adminhtml\Model\Rule\Condition
 */
class Address extends PaymentRestrictionPlugin
{
    /**
     * @param ConditionAddress $subject
     *
     * @return ConditionAddress
     */
    public function afterLoadAttributeOptions(
        ConditionAddress $subject
    ) {
        $actionName = $this->_request->getFullActionName();

        if ($this->_helperData->isEnabled() && $actionName === 'mppaymentrestriction_rule_edit') {
            $attributes = $subject->getAttributeOption();
            if (isset($attributes['payment_method'])) {
                unset($attributes['payment_method']);
                $subject->setAttributeOption($attributes);
            }
        }

        return $subject;
    }
}
