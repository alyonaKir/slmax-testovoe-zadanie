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

namespace Mageplaza\PaymentRestriction\Plugin\Adminhtml\Model;

use Magento\SalesRule\Model\RulesApplier as RulesApplierPlugin;
use Mageplaza\PaymentRestriction\Plugin\PaymentRestrictionPlugin;

/**
 * Class RulesApplier
 * @package Mageplaza\PaymentRestriction\Plugin\Adminhtml\Model
 */
class RulesApplier extends PaymentRestrictionPlugin
{
    /**
     * @param RulesApplierPlugin $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterApplyRules(
        RulesApplierPlugin $subject,
        $result
    ) {
        if ($this->_helperData->isEnabled()) {
            $this->_backendSession->setData('mp_paymentrestriction_applied_rule_ids', $result);
        }

        return $result;
    }
}
