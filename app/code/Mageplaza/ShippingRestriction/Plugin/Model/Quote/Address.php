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

namespace Mageplaza\ShippingRestriction\Plugin\Model\Quote;

use Exception;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Mageplaza\ShippingRestriction\Model\Config\Source\Location;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule\Collection;
use Mageplaza\ShippingRestriction\Model\Rule;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class Address
 * @package Mageplaza\ShippingRestriction\Plugin\Model\Quote
 */
class Address extends ShippingRestrictionPlugin
{
    /**
     * @param QuoteAddress $subject
     * @param array $result
     *
     * @return mixed
     * @throws Exception
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function afterGetGroupedAllShippingRates(
        QuoteAddress $subject,
        $result
    ) {
        $this->ruleActive = false;
        $shippingRatesCol = $result;
        /** @var Collection $ruleCollection */
        $ruleCollection = $this->_helperData->getShippingRuleCollection();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //Load product by product id
        $checkoutSession = $objectManager->create('Magento\Checkout\Model\Session');

        $cartId  = $this->_coreRegistry->registry('mp_shippingrestriction_cart');
        $address = $this->_coreRegistry->registry('mp_shippingrestriction_address');

        if(!$cartId) {
            $cartId = $checkoutSession->getQuote()->getId();
        }

        if(!$address) {
            $address = $checkoutSession->getQuote()->getShippingAddress();
        }


        if ($cartId && $this->_helperData->isEnabled($this->_store->getStore()->getId())) {
            /** @var Quote $quote */
            $quote = $this->_cartRepository->getActive($cartId);
            $this->getFrontendAppliedRule($quote, $address, $ruleCollection);
            if ($this->appliedRule) {
                $appliedShipMethod = $this->appliedRule->getShippingMethods();
                $appliedShipMethod = explode(',', $appliedShipMethod);
                $locations         = explode(',', $this->appliedRule->getLocation());
                if (in_array((string) Location::ORDER_FRONTEND, $locations, true)) {
                    $this->processShippingMethod($shippingRatesCol, $appliedShipMethod);
                }
            }
        }

        return $shippingRatesCol;
    }

    /**
     * @param Quote $quote
     * @param ExtensibleDataInterface $address
     * @param Collection $ruleCollection
     *
     * @throws NoSuchEntityException
     */
    public function getFrontendAppliedRule($quote, $address, $ruleCollection)
    {
        $appliedSaleRuleIds = $quote->getShippingAddress()->getAppliedRuleIds();
        $appliedSaleRuleIds = explode(',', $appliedSaleRuleIds);
        $shippingAddress    = $quote->getShippingAddress();
        if ($address) {
            $shippingAddress->addData($this->_extractAddressData($address));
        }
        $shippingAddress->setCollectShippingRates(true);

        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            if (!$this->_helperData->isInScheduled($rule)) {
                continue;
            }
            $this->getInactiveRule($rule, $appliedSaleRuleIds);
            if ($this->ruleActive) {
                break;
            }
            $this->getActiveRule($rule, $appliedSaleRuleIds);
            if ($this->ruleActive) {
                break;
            }
            if ($rule->validate($shippingAddress)) {
                $this->appliedRule = $rule;
                break;
            }
        }
    }
}
