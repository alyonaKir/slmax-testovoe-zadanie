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

use Mageplaza\Osc\Model\CheckoutManagement as CheckoutManagementPlugin;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;

/**
 * Class TotalsInformationManagement
 * @package Mageplaza\ShippingRules\Plugin\Model
 */
class CheckoutManagement extends ShippingRulesPlugin
{
    /**
     * @param CheckoutManagementPlugin $subject
     * @param $cartId
     * @param $itemId
     * @param $itemQty
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeUpdateItemQty(CheckoutManagementPlugin $subject, $cartId, $itemId, $itemQty)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $this->_coreRegistry->register('mp_shippingrules_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrules_address', $address);
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param $cartId
     * @param $itemId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeRemoveItemById(CheckoutManagementPlugin $subject, $cartId, $itemId)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $this->_coreRegistry->register('mp_shippingrules_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrules_address', $address);
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param $cartId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeGetPaymentTotalInformation(CheckoutManagementPlugin $subject, $cartId)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $this->_coreRegistry->register('mp_shippingrules_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrules_address', $address);
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param $cartId
     * @param $isUseGiftWrap
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeUpdateGiftWrap(CheckoutManagementPlugin $subject, $cartId, $isUseGiftWrap)
    {
        if ($this->_helperData->isEnabled()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $this->_coreRegistry->register('mp_shippingrules_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrules_address', $address);
        }
    }
}
