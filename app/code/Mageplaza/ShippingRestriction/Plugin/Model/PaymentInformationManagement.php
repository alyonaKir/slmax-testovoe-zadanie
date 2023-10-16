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

namespace Mageplaza\ShippingRestriction\Plugin\Model;

use Magento\Checkout\Model\PaymentInformationManagement as PaymentSavingShippingInformationManagement;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class PaymentInformationManagement
 *
 * @package Mageplaza\ShippingRestriction\Plugin\Model
 */
class PaymentInformationManagement extends ShippingRestrictionPlugin
{
    /**
     * @param PaymentSavingShippingInformationManagement $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @SuppressWarnings(Unused)
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentSavingShippingInformationManagement $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        if ($this->_helperData->isEnabled()) {
            $this->_coreRegistry->register('mp_shippingrestriction_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrestriction_address', $billingAddress);
        }
    }
}
