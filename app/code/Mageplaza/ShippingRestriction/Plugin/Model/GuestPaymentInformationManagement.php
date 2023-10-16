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

use Magento\Checkout\Model\GuestPaymentInformationManagement as PaymentGuestSavingShippingInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMask;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class GuestPaymentInformationManagement
 * @package Mageplaza\ShippingRestriction\Plugin\Model
 */
class GuestPaymentInformationManagement extends ShippingRestrictionPlugin
{
    /**
     * @param PaymentGuestSavingShippingInformationManagement $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentGuestSavingShippingInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        if ($this->_helperData->isEnabled()) {
            /** @var QuoteIdMask $quoteIdMask */
            $quoteIdMask = $this->_quoteIdMaskFactory->create();
            $this->_quoteIdMaskResource->load($quoteIdMask, $cartId, 'masked_id');
            $quoteId = (int) $quoteIdMask->getQuoteId();

            $this->_collectTotals($quoteId, $billingAddress);
            $this->_coreRegistry->register('mp_shippingrestriction_cart', $quoteId);
            $this->_coreRegistry->register('mp_shippingrestriction_address', $billingAddress);
        }
    }
}
