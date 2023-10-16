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

use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Mageplaza\ShippingRules\Helper\Data as HelperData;
use Mageplaza\ShippingRules\Plugin\Model\Quote\Address;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;

/**
 * Class ShippingInformationManagement
 * @package Mageplaza\ShippingRules\Plugin\Model
 */
class RePaymentMethod extends ShippingRulesPlugin
{
    /**
     * @var Quote\Address
     */
    public $addressPlugin;
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * RePaymentMethod constructor.
     *
     * @param Registry $coreRegistry
     * @param TotalsCollector $totalsCollector
     * @param CartRepositoryInterface $cartRepository
     * @param BackendSession $backendSession
     * @param QuoteSession $quoteSession
     * @param AddressRepositoryInterface $addressRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param HelperData $helperData
     * @param Address $addressPlugin
     * @param PriceCurrencyInterface $priceCurrency
     * @param DataObjectProcessor|null $dataProcessor
     */
    public function __construct(
        Registry $coreRegistry,
        TotalsCollector $totalsCollector,
        CartRepositoryInterface $cartRepository,
        BackendSession $backendSession,
        QuoteSession $quoteSession,
        AddressRepositoryInterface $addressRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        HelperData $helperData,
        Address $addressPlugin,
        PriceCurrencyInterface $priceCurrency,
        DataObjectProcessor $dataProcessor = null
    ) {
        $this->addressPlugin = $addressPlugin;
        $this->priceCurrency = $priceCurrency;

        parent::__construct(
            $coreRegistry,
            $totalsCollector,
            $cartRepository,
            $backendSession,
            $quoteSession,
            $addressRepository,
            $quoteIdMaskFactory,
            $helperData,
            $dataProcessor
        );
    }

    /**
     * @param CartTotalRepositoryInterface $subject
     * @param TotalsInterface $quoteTotals
     * @param int $cartId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGet(
        CartTotalRepositoryInterface $subject,
        TotalsInterface $quoteTotals,
        $cartId
    ) {
        if ($this->_helperData->isEnabled()
            && !$this->_coreRegistry->registry('mp_shippingrules_cart')
            && $this->_helperData->versionCompare('2.3.5')
        ) {
            $quote    = $this->_cartRepository->getActive($cartId);
            $storeId  = $quote->getStoreId();
            $currency = $quote->getCurrency();
            if (!$quote->getIsVirtual() && ($shipping = $quote->getShippingAddress())) {
                $shippingRatesCollection    = $shipping->getAllShippingRates();
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
                            $newBaseShippingAmount = $this->_priceCalculation(
                                $rule,
                                (float)$shippingRate->getPrice(),
                                $totals,
                                $quote
                            );

                            $baseAmount     = $newBaseShippingAmount - $baseShippingAmountOriginal;
                            $amount         = $this->priceCurrency->convert($baseAmount, $storeId, $currency);
                            $totalSegments  = $quoteTotals->getTotalSegments();
                            if (isset($totalSegments['grand_total'])) {
                                $totalSegments['grand_total']->setValue(
                                    $totalSegments['grand_total']->getValue() + $amount
                                );
                            }
                            if (isset($totalSegments['shipping'])) {
                                $totalSegments['shipping']->setValue($totalSegments['shipping']->getValue() + $amount);
                            }

                            $quoteTotals->setShippingAmount($quoteTotals->getShippingAmount() + $amount);
                            $quoteTotals->setBaseShippingAmount($quoteTotals->getBaseShippingAmount() + $baseAmount);
                            $quoteTotals->setShippingInclTax($quoteTotals->getShippingInclTax() + $amount);
                            $quoteTotals->setBaseShippingInclTax($quoteTotals->getBaseShippingInclTax() + $baseAmount);
                            $quoteTotals->setGrandTotal($quoteTotals->getGrandTotal() + $amount);
                            $quoteTotals->setBaseGrandTotal($quoteTotals->getBaseGrandTotal() + $baseAmount);
                        }
                    }
                }
            }
        }

        return $quoteTotals;
    }
}
