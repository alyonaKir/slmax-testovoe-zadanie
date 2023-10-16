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

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\ShippingMethodManagement as QuoteShippingMethod;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;

/**
 * Class ShippingMethodManagement
 * @package Mageplaza\ShippingRules\Plugin\Model
 */
class ShippingMethodManagement extends ShippingRulesPlugin
{
    /**
     * @param QuoteShippingMethod $subject
     * @param $cartId
     * @param AddressInterface $address
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeEstimateByExtendedAddress(QuoteShippingMethod $subject, $cartId, AddressInterface $address)
    {
        if ($this->_helperData->isEnabled()) {
            $this->_collectTotals($cartId, $address);
            $this->_coreRegistry->register('mp_shippingrules_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrules_address', $address);
        }
    }

    /**
     * @param QuoteShippingMethod $subject
     * @param $cartId
     * @param $addressId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeEstimateByAddressId(QuoteShippingMethod $subject, $cartId, $addressId)
    {
        if ($this->_helperData->isEnabled()) {
            $address = $this->_addressRepository->getById($addressId);
            $this->_collectTotals($cartId, $address);
            $this->_coreRegistry->register('mp_shippingrules_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrules_address', $address);
        }
    }

    /**
     * @param QuoteShippingMethod $subject
     * @param $cartId
     * @param EstimateAddressInterface $address
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeEstimateByAddress(QuoteShippingMethod $subject, $cartId, EstimateAddressInterface $address)
    {
        if ($this->_helperData->isEnabled()) {
            $this->_collectTotals($cartId, $address);
            $this->_coreRegistry->register('mp_shippingrules_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrules_address', $address);
        }
    }
}
