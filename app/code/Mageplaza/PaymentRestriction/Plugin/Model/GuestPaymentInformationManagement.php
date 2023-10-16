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

namespace Mageplaza\PaymentRestriction\Plugin\Model;

use Magento\Checkout\Model\GuestPaymentInformationManagement as PaymentGuestSavingShippingInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Mageplaza\PaymentRestriction\Plugin\PaymentRestrictionPlugin;

/**
 * Class GuestPaymentInformationManagement
 * @package Mageplaza\PaymentRestriction\Plugin\Model
 */
class GuestPaymentInformationManagement extends PaymentRestrictionPlugin
{
    /**
     * @param PaymentGuestSavingShippingInformationManagement $subject
     * @param $cartId
     *
     * @throws NoSuchEntityException
     */
    public function beforeGetPaymentInformation(PaymentGuestSavingShippingInformationManagement $subject, $cartId)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var $quoteIdMask QuoteIdMask */
            $quoteIdMask = $this->_quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quoteId     = (int) $quoteIdMask->getQuoteId();

            $this->_collectTotals($quoteId);
            /** @var Quote $quote */
            $quote = $this->_cartRepository->getActive($quoteId);
            $this->_coreRegistry->unregister('mp_paymentrestriction_quote');
            $this->_coreRegistry->register('mp_paymentrestriction_quote', $quote);
        }
    }
}
