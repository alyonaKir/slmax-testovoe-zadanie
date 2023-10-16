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

namespace Mageplaza\ShippingRules\Plugin\Controller\ShippingCost;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Mageplaza\ShippingRules\Plugin\Model\Quote\Address;

/**
 * Class Calculate
 * @package Mageplaza\ShippingRules\Plugin\Controller\ShippingCost
 */
class Calculate extends Address
{
    /**
     * @param \Mageplaza\ShippingCost\Controller\Index\Calculate $subject
     * @param Rate[] $result
     *
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterGetRates(\Mageplaza\ShippingCost\Controller\Index\Calculate $subject, array $result)
    {
        if (!$this->_helperData->isEnabled()) {
            return $result;
        }

        $this->ruleActive = false;

        /** @var Quote $quote */
        $quote = $this->_checkoutSession->getQuote();

        $totals = $this->getTotals($quote);

        foreach ($this->getAppliedRule($quote) as $rule) {
            if (!$rule) {
                continue;
            }

            $appliedShippingMethod = explode(',', $rule->getShippingMethods());
            foreach ($result as $shippingRate) {
                /** @var Rate $shippingRate */
                if ($this->canUpdatePrice($shippingRate, $appliedShippingMethod, $rule, $quote->getStoreId())) {
                    $newPrice = $this->_priceCalculation($rule, (float)$shippingRate->getPrice(), $totals, $quote);
                    $shippingRate->setPrice($newPrice);
                }
            }
        }

        return $result;
    }
}
