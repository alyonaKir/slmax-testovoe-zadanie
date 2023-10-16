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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Mageplaza\Osc\Model\CheckoutManagement as CheckoutManagementPlugin;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class CheckoutManagement
 * @package Mageplaza\ShippingRestriction\Plugin\Model
 */
class CheckoutManagement extends ShippingRestrictionPlugin
{
    /**
     * @param CheckoutManagementPlugin $subject
     * @param int $cartId
     * @param int $itemId
     * @param int $itemQty
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function beforeUpdateItemQty(
        CheckoutManagementPlugin $subject,
        $cartId,
        $itemId,
        $itemQty
    ) {
        if ($this->_helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote   = $this->_cartRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $this->addMpRegister($cartId, $address);
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param int $cartId
     * @param int $itemId
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function beforeRemoveItemById(
        CheckoutManagementPlugin $subject,
        $cartId,
        $itemId
    ) {
        if ($this->_helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote   = $this->_cartRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $this->addMpRegister($cartId, $address);
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param int $cartId
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function beforeGetPaymentTotalInformation(
        CheckoutManagementPlugin $subject,
        $cartId
    ) {
        if ($this->_helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote   = $this->_cartRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $this->addMpRegister($cartId, $address);
        }
    }

    /**
     * @param CheckoutManagementPlugin $subject
     * @param int $cartId
     * @param bool $isUseGiftWrap
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function beforeUpdateGiftWrap(
        CheckoutManagementPlugin $subject,
        $cartId,
        $isUseGiftWrap
    ) {
        if ($this->_helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote   = $this->_cartRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $this->addMpRegister($cartId, $address);
        }
    }

    /**
     * @param $cartId
     * @param $address
     */
    protected function addMpRegister($cartId, $address)
    {
        if (!$this->_coreRegistry->registry('mp_shippingrestriction_cart')) {
            $this->_coreRegistry->register('mp_shippingrestriction_cart', $cartId);
        }
        if (!$this->_coreRegistry->registry('mp_shippingrestriction_address')) {
            $this->_coreRegistry->register('mp_shippingrestriction_address', $address);
        }
    }
}
