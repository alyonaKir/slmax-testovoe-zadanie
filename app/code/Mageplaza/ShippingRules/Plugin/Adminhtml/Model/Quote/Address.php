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

namespace Mageplaza\ShippingRules\Plugin\Adminhtml\Model\Quote;

use Magento\Backend\Model\Session;
use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Mageplaza\ShippingRules\Helper\Data as HelperData;
use Mageplaza\ShippingRules\Model\ResourceModel\Rule\Collection;
use Mageplaza\ShippingRules\Model\Rule;
use Mageplaza\ShippingRules\Model\RuleFactory as ShippingRuleFactory;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;

/**
 * Class Address
 * @package Mageplaza\ShippingRules\Plugin\Adminhtml\Model\Quote
 */
class Address extends ShippingRulesPlugin
{
    /**
     * @var ShippingRuleFactory
     */
    protected $_shippingRuleFactory;

    /**
     * @var array
     */
    protected $appliedRule;

    /**
     * @var bool
     */
    protected $ruleActive = false;

    /**
     * @var array
     */
    protected $shippingCode = [];

    /**
     * @var array
     */
    protected $shippingData = [];

    /**
     * Address constructor.
     *
     * @param Registry $coreRegistry
     * @param TotalsCollector $totalsCollector
     * @param CartRepositoryInterface $cartRepository
     * @param Session $backendSession
     * @param Quote $quoteSession
     * @param AddressRepositoryInterface $addressRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param HelperData $helperData
     * @param ShippingRuleFactory $shippingRuleFactory
     * @param DataObjectProcessor|null $dataProcessor
     */
    public function __construct(
        Registry $coreRegistry,
        TotalsCollector $totalsCollector,
        CartRepositoryInterface $cartRepository,
        Session $backendSession,
        Quote $quoteSession,
        AddressRepositoryInterface $addressRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        HelperData $helperData,
        ShippingRuleFactory $shippingRuleFactory,
        DataObjectProcessor $dataProcessor = null
    ) {
        $this->_shippingRuleFactory = $shippingRuleFactory;

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
     * @param QuoteAddress $subject
     * @param callable $proceed
     *
     * @return mixed
     * @throws \Exception
     */
    public function aroundGetAllShippingRates(QuoteAddress $subject, callable $proceed)
    {
        $shippingRatesCollection = $proceed();
        $newShippingRatesCollection = $proceed();

        if ($this->_helperData->getConfigGeneral('backend_order') && $this->_helperData->isEnabled()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->_quoteSession->getQuote();
            /** @var Collection $ruleCollection */
            $ruleCollection = $this->_helperData->getShippingRuleCollection($quote->getCustomerGroupId());

            $cartId = $quote->getId();
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true);
            $totals['subtotal'] = (float)$quote->getBaseSubtotal();
            $totals['discount'] = $shippingAddress->getBaseDiscountAmount();
            $totals['final_tax'] =
                $shippingAddress->getBaseTaxAmount() + $shippingAddress->getBaseDiscountTaxCompensationAmount();

            if ($cartId) {
                $appliedSaleRuleIds = $this->_backendSession->getData('mp_applied_rule_ids');
                if (!is_array($appliedSaleRuleIds)) {
                    $appliedSaleRuleIds = [];
                }
                $currentWebsiteId = $quote->getStore()->getWebsiteId();
                /** @var Rule $rule */
                foreach ($ruleCollection as $rule) {
                    if ($this->_helperData->getScheduleFilter($rule, $currentWebsiteId)) {
                        if ($rule->getSaleRulesInactive()) {
                            $saleRuleInactive = explode(',', $rule->getSaleRulesInactive());
                            foreach ($saleRuleInactive as $inActive) {
                                if (in_array($inActive, $appliedSaleRuleIds, true)) {
                                    $this->ruleActive = true;
                                    break;
                                }
                            }
                            if ($this->ruleActive) {
                                $this->appliedRule[] = null;
                                $this->ruleActive = false;
                                continue;
                            }
                        }
                        if ($rule->getSaleRulesActive()) {
                            $saleRuleActive = explode(',', $rule->getSaleRulesActive());
                            foreach ($saleRuleActive as $active) {
                                if (in_array($active, $appliedSaleRuleIds, true)) {
                                    $this->ruleActive = true;
                                    break;
                                }
                            }
                            if ($this->ruleActive) {
                                $this->appliedRule[] = $rule;
                                if ($rule->getDiscardSubRule()) {
                                    break;
                                }
                                $this->ruleActive = false;
                                continue;
                            }
                        }
                        if ($rule->validate($shippingAddress)) {
                            $this->appliedRule[] = $rule;
                            if ($rule->getDiscardSubRule()) {
                                break;
                            }
                            continue;
                        }
                    }
                }

                if (count($this->appliedRule) > 0) {
                    foreach ($this->appliedRule as $rule) {
                        if ($rule) {
                            $appliedShippingMethod = $rule->getShippingMethods();
                            $appliedShippingMethod = explode(',', $appliedShippingMethod);
                            foreach ($shippingRatesCollection as $shippingRates) {
                                $shippingMethodCode = $shippingRates->getCode();
                                if (in_array($shippingMethodCode, $appliedShippingMethod, true)) {
                                    if (!in_array($shippingMethodCode, $this->shippingCode, true)) {
                                        if (!$this->_helperData->getConfigGeneral('multi_rules')) {
                                            $this->shippingCode[] = $shippingMethodCode;
                                        }
                                        array_push($this->shippingData, [
                                            'shipping_code' => $shippingMethodCode,
                                            'shipping_new_price' => $this->_priceCalculation(
                                                $rule,
                                                (float)$shippingRates->getPrice(),
                                                $totals,
                                                $quote
                                            )
                                        ]);
                                    }
                                }
                            }
                            foreach ($newShippingRatesCollection as $shippingRates) {
                                $shippingMethodCode = $shippingRates->getCode();
                                foreach ($this->shippingData as $data) {
                                    if ($shippingMethodCode === $data['shipping_code']) {
                                        $shippingRates->setPrice($data['shipping_new_price']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $newShippingRatesCollection;
    }

    /**
     * @param QuoteAddress $subject
     * @param callable $proceed
     *
     * @return mixed
     * @throws \Exception
     */
    public function aroundGetGroupedAllShippingRates(QuoteAddress $subject, callable $proceed)
    {
        $this->ruleActive = false;
        $shippingRatesCollection = $proceed();
        $newShippingRatesCollection = $proceed();

        if ($this->_helperData->getConfigGeneral('backend_order') && $this->_helperData->isEnabled()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->_quoteSession->getQuote();
            /** @var Collection $ruleCollection */
            $ruleCollection = $this->_helperData->getShippingRuleCollection($quote->getCustomerGroupId());

            $cartId = $quote->getId();
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true);
            $totals['subtotal'] = (float)$quote->getBaseSubtotal();
            $totals['discount'] = $shippingAddress->getBaseDiscountAmount();
            $totals['final_tax'] =
                $shippingAddress->getBaseTaxAmount() + $shippingAddress->getBaseDiscountTaxCompensationAmount();

            if ($cartId) {
                $appliedSaleRuleIds = $this->_backendSession->getData('mp_applied_rule_ids');

                if (!is_array($appliedSaleRuleIds)) {
                    $appliedSaleRuleIds = [];
                }
                $currentWebsiteId = $quote->getStore()->getWebsiteId();
                /** @var Rule $rule */
                foreach ($ruleCollection as $rule) {
                    if ($this->_helperData->getScheduleFilter($rule, $currentWebsiteId)) {
                        if ($rule->getSaleRulesInactive()) {
                            $saleRuleInactive = explode(',', $rule->getSaleRulesInactive());
                            foreach ($saleRuleInactive as $inActive) {
                                if (in_array($inActive, $appliedSaleRuleIds, true)) {
                                    $this->ruleActive = true;
                                    break;
                                }
                            }
                            if ($this->ruleActive) {
                                $this->appliedRule[] = null;
                                $this->ruleActive = false;
                                continue;
                            }
                        }
                        if ($rule->getSaleRulesActive()) {
                            $saleRuleActive = explode(',', $rule->getSaleRulesActive());
                            foreach ($saleRuleActive as $active) {
                                if (in_array($active, $appliedSaleRuleIds, true)) {
                                    $this->ruleActive = true;
                                    break;
                                }
                            }
                            if ($this->ruleActive) {
                                $this->appliedRule[] = $rule;
                                if ($rule->getDiscardSubRule()) {
                                    break;
                                }
                                $this->ruleActive = false;
                                continue;
                            }
                        }
                        if ($rule->validate($shippingAddress)) {
                            $this->appliedRule[] = $rule;
                            if ($rule->getDiscardSubRule()) {
                                break;
                            }
                            continue;
                        }
                    }
                }
                if (count($this->appliedRule) > 0) {
                    foreach ($this->appliedRule as $rule) {
                        if ($rule) {
                            $appliedShippingMethod = $rule->getShippingMethods();
                            $appliedShippingMethod = explode(',', $appliedShippingMethod);
                            foreach ($shippingRatesCollection as $shippingRates) {
                                foreach ($shippingRates as $shippingRate) {
                                    $shippingMethodCode = $shippingRate->getCode();
                                    if (in_array($shippingMethodCode, $appliedShippingMethod, true)) {
                                        if (!in_array($shippingMethodCode, $this->shippingCode, true)) {
                                            if (!$this->_helperData->getConfigGeneral('multi_rules')) {
                                                $this->shippingCode[] = $shippingMethodCode;
                                            }
                                            array_push($this->shippingData, [
                                                'shipping_code' => $shippingMethodCode,
                                                'shipping_new_price' => $this->_priceCalculation(
                                                    $rule,
                                                    (float)$shippingRate->getPrice(),
                                                    $totals,
                                                    $quote
                                                )
                                            ]);
                                        }
                                    }
                                }
                            }
                            foreach ($newShippingRatesCollection as $shippingRates) {
                                foreach ($shippingRates as $shippingRate) {
                                    $shippingMethodCode = $shippingRate->getCode();
                                    foreach ($this->shippingData as $data) {
                                        if ($shippingMethodCode === $data['shipping_code']) {
                                            $shippingRate->setPrice($data['shipping_new_price']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $newShippingRatesCollection;
    }
}
