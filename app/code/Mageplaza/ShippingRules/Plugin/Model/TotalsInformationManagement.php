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

namespace Mageplaza\ShippingRules\Plugin\Model;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\TotalsInformationManagement as QuoteTotalsInformationManagement;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;

/**
 * Class TotalsInformationManagement
 * @package Mageplaza\ShippingRules\Plugin\Model
 */
class TotalsInformationManagement extends ShippingRulesPlugin
{
    /**
     * @param QuoteTotalsInformationManagement $subject
     * @param $cartId
     * @param TotalsInformationInterface $addressInformation
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeCalculate(
        QuoteTotalsInformationManagement $subject,
        $cartId,
        TotalsInformationInterface $addressInformation
    ) {
        if ($this->_helperData->isEnabled()) {
            $address = $addressInformation->getAddress();
            $this->_collectTotals($cartId, $address);
            $this->_coreRegistry->register('mp_shippingrules_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrules_address', $address);
        }
    }
}
