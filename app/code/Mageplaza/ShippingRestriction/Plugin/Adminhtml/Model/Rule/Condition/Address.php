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

namespace Mageplaza\ShippingRestriction\Plugin\Adminhtml\Model\Rule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Address as ConditionAddress;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class Address
 * @package Mageplaza\ShippingRestriction\Plugin\Adminhtml\Model\Rule\Condition
 */
class Address extends ShippingRestrictionPlugin
{
    /**
     * @param ConditionAddress $subject
     *
     * @return ConditionAddress
     */
    public function afterLoadAttributeOptions(ConditionAddress $subject)
    {
        $actionName = $this->_request->getFullActionName();
        if (($actionName === 'mpshippingrestriction_rule_edit'
                || $actionName === 'mpshippingrestriction_condition_newConditionHtml')
            && $this->_helperData->isEnabled()) {
            $attributes = $subject->getAttributeOption();
            if (!array_key_exists('payment_method', $attributes)) {
                $attributes['payment_method'] = __('Payment Method');
            }
            if (isset($attributes['shipping_method'])) {
                unset($attributes['shipping_method']);
            }
            $subject->setAttributeOption($attributes);
        }

        return $subject;
    }
}
