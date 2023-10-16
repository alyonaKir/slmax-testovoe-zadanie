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

namespace Mageplaza\ShippingRestriction\Plugin\Adminhtml\Model;

use Magento\SalesRule\Model\RulesApplier as RulesApplierPlugin;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class RulesApplier
 * @package Mageplaza\ShippingRestriction\Plugin\Adminhtml\Model
 */
class RulesApplier extends ShippingRestrictionPlugin
{
    /**
     * @param RulesApplierPlugin $subject
     * @param array $result
     *
     * @return mixed
     * @SuppressWarnings(Unused)
     */
    public function afterApplyRules(
        RulesApplierPlugin $subject,
        $result
    ) {
        if ($this->_helperData->isEnabled()) {
            $this->_backendSession->setData('mp_shipping_restriction_applied_rule_ids', $result);
        }

        return $result;
    }
}
