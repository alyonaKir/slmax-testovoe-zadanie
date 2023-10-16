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

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement as ShippingInformationManagementPlugin;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Mageplaza\PaymentRestriction\Plugin\PaymentRestrictionPlugin;

/**
 * Class ShippingInformationManagement
 * @package Mageplaza\PaymentRestriction\Plugin\Model
 */
class ShippingInformationManagement extends PaymentRestrictionPlugin
{
    /**
     * @param ShippingInformationManagementPlugin $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementPlugin $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($this->_helperData->isEnabled()) {
            $this->_collectTotals($cartId);
            /** @var Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $this->_coreRegistry->register('mp_paymentrestriction_quote', $quote);
        }
    }
}
