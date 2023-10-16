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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Mageplaza\Osc\Model\CheckoutManagement as CheckoutManagementPlugin;
use Mageplaza\PaymentRestriction\Plugin\PaymentRestrictionPlugin;

/**
 * Class CheckoutManagement
 * @package Mageplaza\PaymentRestriction\Plugin\Model
 */
class CheckoutManagement extends PaymentRestrictionPlugin
{
    /**
     * @param CheckoutManagementPlugin $subject
     * @param $cartId
     * @param $itemId
     * @param $itemQty
     *
     * @throws NoSuchEntityException
     */
    public function beforeUpdateItemQty(CheckoutManagementPlugin $subject, $cartId, $itemId, $itemQty)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            if ($itemQty != 0) {
                $this->_coreRegistry->register('mp_paymentrestriction_quote', $quote);
            }
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param $cartId
     * @param $itemId
     *
     * @throws NoSuchEntityException
     */
    public function beforeRemoveItemById(CheckoutManagementPlugin $subject, $cartId, $itemId)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $this->_coreRegistry->register('mp_paymentrestriction_quote', $quote);
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param $cartId
     *
     * @throws NoSuchEntityException
     */
    public function beforeGetPaymentTotalInformation(CheckoutManagementPlugin $subject, $cartId)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $this->_coreRegistry->register('mp_paymentrestriction_quote', $quote);
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param $cartId
     * @param $isUseGiftWrap
     *
     * @throws NoSuchEntityException
     */
    public function beforeUpdateGiftWrap(CheckoutManagementPlugin $subject, $cartId, $isUseGiftWrap)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $this->_coreRegistry->register('mp_paymentrestriction_quote', $quote);
        }
    }
}
