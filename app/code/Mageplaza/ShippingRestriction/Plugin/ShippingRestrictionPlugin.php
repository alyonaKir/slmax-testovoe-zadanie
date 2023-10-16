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

namespace Mageplaza\ShippingRestriction\Plugin;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ShippingRestriction\Helper\Data as HelperData;
use Mageplaza\ShippingRestriction\Model\Rule;

/**
 * Class ShippingRestrictionPlugin
 * @package Mageplaza\ShippingRestriction\Plugin
 */
class ShippingRestrictionPlugin
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var TotalsCollector
     */
    protected $_totalsCollector;

    /**
     * Data object processor for array serialization using class reflection.
     *
     * @var DataObjectProcessor $dataProcessor
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
     * @var QuoteIdMaskResource
     */
    protected $_quoteIdMaskResource;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var bool|Rule
     */
    protected $appliedRule;

    /**
     * @var bool
     */
    protected $ruleActive = false;

    /**
     * @var StoreManagerInterface
     */
    protected $_store;

    /**
     * ShippingRestrictionPlugin constructor.
     *
     * @param Registry $coreRegistry
     * @param RequestInterface $request
     * @param TotalsCollector $totalsCollector
     * @param CartRepositoryInterface $cartRepository
     * @param BackendSession $backendSession
     * @param QuoteSession $quoteSession
     * @param AddressRepositoryInterface $addressRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResource $quoteIdMaskResource
     * @param HelperData $helperData
     * @param StoreManagerInterface $_store
     * @param DataObjectProcessor|null $dataProcessor
     */
    public function __construct(
        Registry $coreRegistry,
        RequestInterface $request,
        TotalsCollector $totalsCollector,
        CartRepositoryInterface $cartRepository,
        BackendSession $backendSession,
        QuoteSession $quoteSession,
        AddressRepositoryInterface $addressRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource,
        HelperData $helperData,
        StoreManagerInterface $_store,
        DataObjectProcessor $dataProcessor = null
    ) {
        $this->_coreRegistry        = $coreRegistry;
        $this->_request             = $request;
        $this->_cartRepository      = $cartRepository;
        $this->_totalsCollector     = $totalsCollector;
        $this->_backendSession      = $backendSession;
        $this->_quoteSession        = $quoteSession;
        $this->_addressRepository   = $addressRepository;
        $this->_quoteIdMaskFactory  = $quoteIdMaskFactory;
        $this->_quoteIdMaskResource = $quoteIdMaskResource;
        $this->_helperData          = $helperData;
        $this->_store               = $_store;
        $this->_dataProcessor       = $dataProcessor ?: ObjectManager::getInstance()->get(DataObjectProcessor::class);
    }

    /**
     * @param int $cartId
     * @param AddressInterface|EstimateAddressInterface $address
     *
     * @throws NoSuchEntityException
     */
    protected function _collectTotals($cartId, $address)
    {
        /** @var Quote $quote */
        $quote           = $this->_cartRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $this->_totalsCollector->collectAddressTotals($quote, $shippingAddress);
    }

    /**
     * Get transform address interface into Array.
     *
     * @param ExtensibleDataInterface $address
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
     * @param Rule $rule
     * @param array $appliedSaleRuleIds
     */
    public function getInactiveRule($rule, $appliedSaleRuleIds)
    {
        if ($rule->getSaleRulesInactive()) {
            $saleRuleInactive = explode(',', $rule->getSaleRulesInactive());
            foreach ($saleRuleInactive as $inActive) {
                if (in_array($inActive, $appliedSaleRuleIds, true)) {
                    $this->ruleActive = true;
                    break;
                }
            }
            if ($this->ruleActive) {
                $this->appliedRule = null;
            }
        }
    }

    /**
     * @param Rule $rule
     * @param array $appliedSaleRuleIds
     */
    public function getActiveRule($rule, $appliedSaleRuleIds)
    {
        if ($rule->getSaleRulesActive()) {
            $saleRuleActive = explode(',', $rule->getSaleRulesActive());
            foreach ($saleRuleActive as $active) {
                if (in_array($active, $appliedSaleRuleIds, true)) {
                    $this->ruleActive = true;
                    break;
                }
            }
            if ($this->ruleActive) {
                $this->appliedRule = $rule;
            }
        }
    }

    /**
     * @param array $shippingRatesCol
     * @param array $appliedShipMethod
     */
    public function processShippingMethod(&$shippingRatesCol, $appliedShipMethod)
    {
        /** @var array $shippingRates */
        foreach ($shippingRatesCol as $title => &$shippingRates) {
            foreach ($shippingRates as $key => $shippingRate) {
                $shippingMethodCode = $shippingRate->getCode();
                if (in_array($shippingMethodCode, $appliedShipMethod, true) && !$this->appliedRule->getAction()) {
                    unset($shippingRates[$key]);
                }
                if (!in_array($shippingMethodCode, $appliedShipMethod, true) && $this->appliedRule->getAction()) {
                    unset($shippingRates[$key]);
                }
            }
            if (empty($shippingRates)) {
                unset($shippingRatesCol[$title]);
            }
        }
    }
}
