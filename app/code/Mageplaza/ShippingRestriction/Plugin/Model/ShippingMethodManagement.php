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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\ShippingMethodManagement as QuoteShippingMethod;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class ShippingMethodManagement
 * @package Mageplaza\ShippingRestriction\Plugin\Model
 */
class ShippingMethodManagement extends ShippingRestrictionPlugin
{
    /**
     * @param QuoteShippingMethod $subject
     * @param mixed $cartId
     * @param AddressInterface $address
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function beforeEstimateByExtendedAddress(
        QuoteShippingMethod $subject,
        $cartId,
        AddressInterface $address
    ) {
        if ($this->_helperData->isEnabled()) {
            $this->_collectTotals($cartId, $address);
            $this->_coreRegistry->register('mp_shippingrestriction_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrestriction_address', $address);
        }
    }

    /**
     * @param QuoteShippingMethod $subject
     * @param int $cartId
     * @param int $addressId
     *
     * @throws LocalizedException
     * @SuppressWarnings(Unused)
     */
    public function beforeEstimateByAddressId(
        QuoteShippingMethod $subject,
        $cartId,
        $addressId
    ) {
        if ($this->_helperData->isEnabled()) {
            /** @var AddressInterface $address */
            $address = $this->_addressRepository->getById($addressId);
            $this->_collectTotals($cartId, $address);
            $this->_coreRegistry->register('mp_shippingrestriction_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrestriction_address', $address);
        }
    }

    /**
     * @param QuoteShippingMethod $subject
     * @param int $cartId
     * @param EstimateAddressInterface $address
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function beforeEstimateByAddress(
        QuoteShippingMethod $subject,
        $cartId,
        EstimateAddressInterface $address
    ) {
        if ($this->_helperData->isEnabled()) {
            $this->_collectTotals($cartId, $address);
            $this->_coreRegistry->register('mp_shippingrestriction_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrestriction_address', $address);
        }
    }
}
