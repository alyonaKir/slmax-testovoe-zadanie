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

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement as PaymentShippingInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class ShippingInformationManagement
 * @package Mageplaza\ShippingRestriction\Plugin\Model
 */
class ShippingInformationManagement extends ShippingRestrictionPlugin
{
    /**
     * @param PaymentShippingInformationManagement $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function beforeSaveAddressInformation(
        PaymentShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($this->_helperData->isEnabled()) {
            $address = $addressInformation->getShippingAddress();
            $this->_collectTotals($cartId, $address);
            $this->_coreRegistry->register('mp_shippingrestriction_cart', $cartId);
            $this->_coreRegistry->register('mp_shippingrestriction_address', $address);
        }
    }
}
