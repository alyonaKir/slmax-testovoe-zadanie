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

namespace Mageplaza\ShippingRules\Plugin\Adminhtml\Model;

use Magento\SalesRule\Model\RulesApplier as RulesApplierPlugin;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;

/**
 * Class RulesApplier
 * @package Mageplaza\ShippingRules\Plugin\Adminhtml\Model
 */
class RulesApplier extends ShippingRulesPlugin
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
        if ($this->_helperData->getConfigGeneral('backend_order') && $this->_helperData->isEnabled()) {
            $this->_backendSession->setData('mp_applied_rule_ids', $result);
        }

        return $result;
    }
}
