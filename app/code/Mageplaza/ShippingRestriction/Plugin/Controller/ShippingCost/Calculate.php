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

namespace Mageplaza\ShippingRestriction\Plugin\Controller\ShippingCost;

use Exception;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ShippingRestriction\Helper\Data as HelperData;
use Mageplaza\ShippingRestriction\Model\Config\Source\Location;
use Mageplaza\ShippingRestriction\Plugin\Model\Quote\Address;

/**
 * Class Calculate
 * @package Mageplaza\ShippingRestriction\Plugin\Controller\ShippingCost
 */
class Calculate extends Address
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * Calculate constructor.
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
     * @param Session $checkoutSession
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
        Session $checkoutSession,
        DataObjectProcessor $dataProcessor = null
    ) {
        $this->checkoutSession = $checkoutSession;

        parent::__construct(
            $coreRegistry,
            $request,
            $totalsCollector,
            $cartRepository,
            $backendSession,
            $quoteSession,
            $addressRepository,
            $quoteIdMaskFactory,
            $quoteIdMaskResource,
            $helperData,
            $_store,
            $dataProcessor
        );
    }

    /**
     * @param \Mageplaza\ShippingCost\Controller\Index\Calculate $subject
     * @param Rate[] $result
     *
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function afterGetRates(\Mageplaza\ShippingCost\Controller\Index\Calculate $subject, array $result)
    {
        $this->ruleActive = false;

        if (!$this->_helperData->isEnabled($this->_store->getStore()->getId())) {
            return $result;
        }

        $this->getFrontendAppliedRule(
            $this->checkoutSession->getQuote(),
            $this->_coreRegistry->registry('mp_shippingrestriction_address'),
            $this->_helperData->getShippingRuleCollection()
        );

        if (!$this->appliedRule) {
            return $result;
        }

        $appliedShipMethod = explode(',', $this->appliedRule->getShippingMethods());
        $locations         = explode(',', $this->appliedRule->getLocation());

        if (in_array((string) Location::ORDER_FRONTEND, $locations, true)) {
            $result = [$result];

            $this->processShippingMethod($result, $appliedShipMethod);

            return $result[0] ?? [];
        }

        return $result;
    }
}
