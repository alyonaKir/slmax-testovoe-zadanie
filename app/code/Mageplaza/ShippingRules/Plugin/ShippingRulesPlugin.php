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

namespace Mageplaza\ShippingRules\Plugin;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Mageplaza\ShippingRules\Helper\Data as HelperData;
use Mageplaza\ShippingRules\Model\Config\Source\CalculationFee;
use Mageplaza\ShippingRules\Model\Config\Source\CartScope\Type as CartScopeType;
use Mageplaza\ShippingRules\Model\Config\Source\ExtraFee;
use Mageplaza\ShippingRules\Model\Config\Source\OrderScope\Type as OrderScopeType;

/**
 * Class ShippingRulesPlugin
 * @package Mageplaza\ShippingRules\Plugin
 */
class ShippingRulesPlugin
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $_totalsCollector;

    /**
     * Data object processor for array serialization using class reflection.
     *
     * @var \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     */
    protected $_dataProcessor;

    /**
     * @var CartRepositoryInterface
     */
    protected $_cartRepository;

    /**
     * @var BackendSession
     */
    protected $_backendSession;

    /**
     * @var QuoteSession
     */
    protected $_quoteSession;

    /**
     * @var AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $_quoteIdMaskFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * ShippingRulesPlugin constructor.
     *
     * @param Registry $coreRegistry
     * @param TotalsCollector $totalsCollector
     * @param CartRepositoryInterface $cartRepository
     * @param BackendSession $backendSession
     * @param QuoteSession $quoteSession
     * @param AddressRepositoryInterface $addressRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param HelperData $helperData
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
        DataObjectProcessor $dataProcessor = null
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_cartRepository = $cartRepository;
        $this->_totalsCollector = $totalsCollector;
        $this->_backendSession = $backendSession;
        $this->_quoteSession = $quoteSession;
        $this->_addressRepository = $addressRepository;
        $this->_quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->_helperData = $helperData;
        $this->_dataProcessor = $dataProcessor ?: ObjectManager::getInstance()->get(DataObjectProcessor::class);
    }

    /**
     * @param $cartId
     * @param $address
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _collectTotals($cartId, $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_cartRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($this->_extractAddressData($address));
        $shippingAddress->setCollectShippingRates(true);
        $this->_totalsCollector->collectAddressTotals($quote, $shippingAddress);
    }

    /**
     * Get transform address interface into Array.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     *
     * @return array
     */
    protected function _extractAddressData($address)
    {
        $className = CustomerAddressInterface::class;
        if ($address instanceof AddressInterface) {
            $className = AddressInterface::class;
        } elseif ($address instanceof EstimateAddressInterface) {
            $className = EstimateAddressInterface::class;
        }

        return $this->_dataProcessor->buildOutputDataArray(
            $address,
            $className
        );
    }

    /**
     * @param $rule
     * @param $oldShipPrice
     * @param $totals
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return float|int
     */
    protected function _priceCalculation($rule, $oldShipPrice, $totals, $quote)
    {
        $result = $oldShipPrice;
        $tempResult = 0;
        $tempFeeChange = 0;
        $ruleOrderScope = $this->_helperData->jsonDecode($rule->getOrderScope());
        $ruleCartScope = $this->_helperData->jsonDecode($rule->getCartScope());
        $items = $quote->getAllVisibleItems();
        switch ($rule->getApplyFee()) {
            case CalculationFee::RE_CALCULATION:
                switch ($ruleOrderScope['type']) {
                    case OrderScopeType::DISABLE:
                        $result += 0;
                        break;
                    case OrderScopeType::FIXED_AMOUNT:
                        $result = (float)$ruleOrderScope['fee'];
                        $tempResult = $result;
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);

                        break;
                    case OrderScopeType::PERCENTAGE_OF_ORIGINAL_SHIPPING_FEE:
                        $result = (float)$ruleOrderScope['fee'] * $oldShipPrice / 100;
                        $tempResult = $result;
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);

                        break;
                    case OrderScopeType::PERCENTAGE_OF_CART_TOTAL:
                        $originTotals = $totals['subtotal'];

                        if (isset($ruleOrderScope['extra'])) {
                            foreach ($ruleOrderScope['extra'] as $extra) {
                                if ($extra == ExtraFee::DISCOUNT) {
                                    $originTotals += $totals['discount'];
                                }
                                if ($extra == ExtraFee::TAX) {
                                    $originTotals += $totals['final_tax'];
                                }
                            }
                        }
                        $result = (float)$ruleOrderScope['fee'] * $originTotals / 100;
                        $tempResult = $result;
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);

                        break;
                }
                switch ($ruleCartScope['type']) {
                    case CartScopeType::DISABLE:
                        $result += 0;

                        break;
                    case CartScopeType::FIXED_AMOUNT:
                        $resultChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $resultChange += (float)$ruleCartScope['fee'] * $item->getQty();
                            }
                        }
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $tempResult + $resultChange;
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }
                        break;
                    case CartScopeType::PERCENTAGE_OF_ITEM_PRICE:
                        $resultChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $itemPrice = $item->getQty() * $item->getBasePrice();
                                if (isset($ruleCartScope['extra'])) {
                                    foreach ($ruleCartScope['extra'] as $extra) {
                                        if ($extra == ExtraFee::DISCOUNT) {
                                            $itemPrice -= $item->getBaseDiscountAmount();
                                        }
                                        if ($extra == ExtraFee::TAX) {
                                            $itemPrice += $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensationAmount();
                                        }
                                    }
                                }
                                $resultChange += (float)$ruleCartScope['fee'] * $itemPrice / 100;
                            }
                        }
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $tempResult + $resultChange;
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }
                        break;
                    case CartScopeType::FIXED_AMOUNT_PER_WEIGHT:
                        $resultChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $resultChange += (float)$ruleCartScope['fee'] * $item->getProduct()->getWeight() * $item->getQty();
                            }
                        }
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $tempResult + $resultChange;
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }
                        break;
                }

                return $result;
                break;
            case CalculationFee::ADD_EXTRA_FEE:
                switch ($ruleOrderScope['type']) {
                    case OrderScopeType::DISABLE:
                        $result += 0;

                        break;
                    case OrderScopeType::FIXED_AMOUNT:
                        $feeChange = (float)$ruleOrderScope['fee'];
                        $result = $oldShipPrice + $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                        $tempFeeChange = $feeChange;
                        break;
                    case OrderScopeType::PERCENTAGE_OF_ORIGINAL_SHIPPING_FEE:
                        $feeChange = (float)$ruleOrderScope['fee'] * $oldShipPrice / 100;
                        $result = $oldShipPrice + $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                        $tempFeeChange = $feeChange;
                        break;
                    case OrderScopeType::PERCENTAGE_OF_CART_TOTAL:
                        $feeChange = $totals['subtotal'];
                        if (isset($ruleOrderScope['extra'])) {
                            foreach ($ruleOrderScope['extra'] as $extra) {
                                if ($extra == ExtraFee::DISCOUNT) {
                                    $feeChange += $totals['discount'];
                                }
                                if ($extra == ExtraFee::TAX) {
                                    $feeChange += $totals['final_tax'];
                                }
                            }
                        }
                        $feeChange = (float)$ruleOrderScope['fee'] * $feeChange / 100;
                        $result = $oldShipPrice + $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                        $tempFeeChange = $feeChange;
                        break;
                }
                switch ($ruleCartScope['type']) {
                    case CartScopeType::DISABLE:
                        $result += 0;
                        break;
                    case CartScopeType::FIXED_AMOUNT:
                        $feeChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $feeChange += (float)$ruleCartScope['fee'] * $item->getQty();
                            }
                        }
                        $feeChange += $tempFeeChange;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $oldShipPrice + $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }

                        break;
                    case CartScopeType::PERCENTAGE_OF_ITEM_PRICE:
                        $feeChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $itemPrice = $item->getQty() * $item->getBasePrice();
                                if (isset($ruleCartScope['extra'])) {
                                    foreach ($ruleCartScope['extra'] as $extra) {
                                        if ($extra == ExtraFee::DISCOUNT) {
                                            $itemPrice -= $item->getBaseDiscountAmount();
                                        }
                                        if ($extra == ExtraFee::TAX) {
                                            $itemPrice += $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensationAmount();
                                        }
                                    }
                                }
                                $feeChange += (float)$ruleCartScope['fee'] * $itemPrice / 100;
                            }
                        }
                        $feeChange += $tempFeeChange;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $oldShipPrice + $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }

                        break;
                    case CartScopeType::FIXED_AMOUNT_PER_WEIGHT:
                        $feeChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $feeChange += (float)$ruleCartScope['fee'] * $item->getProduct()->getWeight() * $item->getQty();
                            }
                        }
                        $feeChange += $tempFeeChange;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $oldShipPrice + $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }

                        break;
                }

                return $result;
                break;
            case CalculationFee::SUBTRACT_EXTRA_FEE:
                switch ($ruleOrderScope['type']) {
                    case OrderScopeType::DISABLE:
                        $result -= 0;
                        break;
                    case OrderScopeType::FIXED_AMOUNT:
                        $feeChange = (float)$ruleOrderScope['fee'];
                        $result = $oldShipPrice - $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                        $result = ($result > 0) ? $result : 0;
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                        $tempFeeChange = $feeChange;
                        break;
                    case OrderScopeType::PERCENTAGE_OF_ORIGINAL_SHIPPING_FEE:
                        $feeChange = (float)$ruleOrderScope['fee'] * $oldShipPrice / 100;
                        $result = $oldShipPrice - $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                        $result = ($result > 0) ? $result : 0;
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                        $tempFeeChange = $feeChange;
                        break;
                    case OrderScopeType::PERCENTAGE_OF_CART_TOTAL:
                        $feeChange = $totals['subtotal'];
                        if (isset($ruleOrderScope['extra'])) {
                            foreach ($ruleOrderScope['extra'] as $extra) {
                                if ($extra == ExtraFee::DISCOUNT) {
                                    $feeChange += $totals['discount'];
                                }
                                if ($extra == ExtraFee::TAX) {
                                    $feeChange += $totals['final_tax'];
                                }
                            }
                        }
                        $feeChange = (float)$ruleOrderScope['fee'] * $feeChange / 100;
                        $result = $oldShipPrice - $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                        $result = ($result > 0) ? $result : 0;
                        $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                        $tempFeeChange = $feeChange;
                        break;
                }
                switch ($ruleCartScope['type']) {
                    case CartScopeType::DISABLE:
                        $result += 0;
                        break;
                    case CartScopeType::FIXED_AMOUNT:
                        $feeChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $feeChange += (float)$ruleCartScope['fee'] * $item->getQty();
                            }
                        }
                        $feeChange += $tempFeeChange;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $oldShipPrice - $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                                $result = ($result > 0) ? $result : 0;
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }

                        break;
                    case CartScopeType::PERCENTAGE_OF_ITEM_PRICE:
                        $feeChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $itemPrice = $item->getQty() * $item->getBasePrice();
                                if (isset($ruleCartScope['extra'])) {
                                    foreach ($ruleCartScope['extra'] as $extra) {
                                        if ($extra == ExtraFee::DISCOUNT) {
                                            $itemPrice -= $item->getBaseDiscountAmount();
                                        }
                                        if ($extra == ExtraFee::TAX) {
                                            $itemPrice += $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensationAmount();
                                        }
                                    }
                                }
                                $feeChange += (float)$ruleCartScope['fee'] * $itemPrice / 100;
                            }
                        }
                        $feeChange += $tempFeeChange;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $oldShipPrice - $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                                $result = ($result > 0) ? $result : 0;
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }

                        break;
                    case CartScopeType::FIXED_AMOUNT_PER_WEIGHT:
                        $feeChange = 0;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $feeChange += (float)$ruleCartScope['fee'] * $item->getProduct()->getWeight() * $item->getQty();
                            }
                        }
                        $feeChange += $tempFeeChange;
                        foreach ($items as $item) {
                            if ($this->_validateAppliedRuleForProduct($rule, $item)) {
                                $result = $oldShipPrice - $this->_calculationMinMaxShippingFeeChange($rule, $feeChange);
                                $result = ($result > 0) ? $result : 0;
                                $result = $this->_calculationMinMaxTotalShippingFee($rule, $result);
                                break;
                            }
                        }

                        break;
                }

                return $result;
                break;
            default:
                return $result;
        }
    }

    /**
     * Calculation min-max total shipping fee
     *
     * @param $rule
     * @param $result
     *
     * @return int
     */
    protected function _calculationMinMaxTotalShippingFee($rule, $result)
    {
        if ($rule->getMinTotalFee() >= 0 && is_numeric($rule->getMinTotalFee())) {
            $minTotalFee = (float)$rule->getMinTotalFee();
            $result = ($result <= $minTotalFee) ? $minTotalFee : $result;
        }
        if ($rule->getMaxTotalFee() >= 0 && is_numeric($rule->getMaxTotalFee())) {
            $maxTotalFee = (float)$rule->getMaxTotalFee();
            $result = ($result >= $maxTotalFee) ? $maxTotalFee : $result;
        }

        return $result;
    }

    /**
     * @param $rule
     * @param $feeChange
     *
     * @return float
     */
    protected function _calculationMinMaxShippingFeeChange($rule, $feeChange)
    {
        if ($rule->getMinFeeChange() >= 0 && is_numeric($rule->getMinFeeChange())) {
            $minFeeChange = (float)$rule->getMinFeeChange();
            $feeChange = ($feeChange <= $minFeeChange) ? $minFeeChange : $feeChange;
        }
        if ($rule->getMaxFeeChange() >= 0 && is_numeric($rule->getMaxFeeChange())) {
            $maxFeeChange = (float)$rule->getMaxFeeChange();
            $feeChange = ($feeChange >= $maxFeeChange) ? $maxFeeChange : $feeChange;
        }

        return $feeChange;
    }

    /**
     * @param $rule
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return bool
     */
    protected function _validateAppliedRuleForProduct($rule, $item)
    {
        $isApply = false;

        if ($rule->getActions()->validate($item) && $item->getProduct()->getTypeInstance()->hasWeight()) {
            if (!$rule->getApplyFreeItem()) {
                if (!$item->getFreeShipping()) {
                    $isApply = true;
                }
            } else {
                $isApply = true;
            }
        }

        return $isApply;
    }
}
