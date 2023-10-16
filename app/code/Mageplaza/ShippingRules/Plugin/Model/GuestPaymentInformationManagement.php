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

use Magento\Checkout\Model\GuestPaymentInformationManagement as PaymentGuestSavingShippingInformationManagement;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMask;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;

/**
 * Class ShippingInformationManagement
 * @package Mageplaza\ShippingRules\Plugin\Model
 */
class GuestPaymentInformationManagement extends ShippingRulesPlugin
{
    /**
     * @param PaymentGuestSavingShippingInformationManagement $subject
     * @param $cartId
     * @param $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentGuestSavingShippingInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        if ($this->_helperData->isEnabled()) {
            /** @var $quoteIdMask QuoteIdMask */
            $quoteIdMask = $this->_quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quoteId = (int)$quoteIdMask->getQuoteId();

            $this->_collectTotals($quoteId, $billingAddress);
            $this->_coreRegistry->register('mp_shippingrules_cart', $quoteId);
            $this->_coreRegistry->register('mp_shippingrules_address', $billingAddress);
        }
    }
}
