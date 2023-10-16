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

namespace Mageplaza\ShippingRules\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\Rate;
use Mageplaza\ShippingRules\Helper\Data;
use Mageplaza\ShippingRules\Plugin\Model\Quote\Address;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class PaypalPrepareItems
 * @package Mageplaza\ShippingRules\Observer
 */
class PaypalPrepareItems implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Address
     */
    public $addressPlugin;

    /**
     * @var ShippingRulesPlugin
     */
    private $shippingRulesPlugin;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * PaypalPrepareItems constructor.
     *
     * @param Session $checkoutSession
     * @param Data $helperData
     * @param Address $addressPlugin
     * @param ShippingRulesPlugin $shippingRulesPlugin
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Session $checkoutSession,
        Data $helperData,
        Address $addressPlugin,
        ShippingRulesPlugin $shippingRulesPlugin,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->checkoutSession     = $checkoutSession;
        $this->helperData          = $helperData;
        $this->addressPlugin       = $addressPlugin;
        $this->shippingRulesPlugin = $shippingRulesPlugin;
        $this->priceCurrency       = $priceCurrency;
    }

    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $quote    = $this->checkoutSession->getQuote();
        $storeId  = $quote->getStoreId();
        $currency = $quote->getCurrency();
        if (!$quote->isVirtual() && $this->helperData->isEnabled($storeId)) {
            $shipping                = $quote->getShippingAddress();
            $shippingRatesCollection = $shipping->getAllShippingRates();

            $totals                     = $this->addressPlugin->getTotals($quote);
            $baseShippingAmountOriginal = $shipping->getBaseShippingAmount();
            foreach ($this->addressPlugin->getAppliedRule($quote) as $rule) {
                if (!$rule) {
                    continue;
                }

                $appliedShippingMethod = explode(',', $rule->getShippingMethods());
                foreach ($shippingRatesCollection as $shippingRate) {
                    /** @var Rate $shippingRate */
                    if ($this->addressPlugin->canUpdatePrice($shippingRate, $appliedShippingMethod, $rule, $storeId)
                        && $shipping->getShippingMethod() === $shippingRate->getCode()) {
                        $newBaseShippingAmount = $this->shippingRulesPlugin->_priceCalculation(
                            $rule,
                            (float)$shippingRate->getPrice(),
                            $totals,
                            $quote
                        );
                        $baseAmount            = $newBaseShippingAmount - $baseShippingAmountOriginal;
                        $amount                = $this->priceCurrency->convert($baseAmount, $storeId, $currency);
                        $shipping->setShippingAmount($shipping->getShippingAmount() + $amount);
                        $shipping->setBaseShippingAmount($shipping->getBaseShippingAmount() + $baseAmount);
                        $shipping->setShippingInclTax($shipping->getShippingInclTax() + $amount);
                        $shipping->setBaseShippingInclTax($shipping->getBaseShippingInclTax() + $baseAmount);
                        $quote->setGrandTotal($quote->getGrandTotal() + $amount);
                        $quote->setBaseGrandTotal($quote->getBaseGrandTotal() + $baseAmount);
                    }
                }
            }
        }
    }
}
