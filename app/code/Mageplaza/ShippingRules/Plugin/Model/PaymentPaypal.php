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
use Magento\Checkout\Model\Session as ModelSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Paypal\Model\CartFactory;
use Magento\Paypal\Model\Config;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Mageplaza\ShippingRules\Helper\Data as HelperData;
use Mageplaza\ShippingRules\Plugin\ShippingRulesPlugin;

/**
 * Class ShippingInformationManagement
 * @package Mageplaza\ShippingRules\Plugin\Model
 */
class PaymentPaypal extends ShippingRulesPlugin
{
    /**
     * @var ModelSession
     */
    private $session;

    /**
     * @var CartFactory
     */
    private $_cartFactory;

    /**
     * PaymentPaypal constructor.
     *
     * @param Registry $coreRegistry
     * @param TotalsCollector $totalsCollector
     * @param CartRepositoryInterface $cartRepository
     * @param BackendSession $backendSession
     * @param QuoteSession $quoteSession
     * @param AddressRepositoryInterface $addressRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param HelperData $helperData
     * @param CartFactory $_cartFactory
     * @param ModelSession $session
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
        CartFactory $_cartFactory,
        ModelSession $session,
        DataObjectProcessor $dataProcessor = null
    ) {
        $this->session      = $session;
        $this->_cartFactory = $_cartFactory;
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
     * @param Config $subject
     * @param $result
     * @param DataObject $to
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterExportExpressCheckoutStyleSettings(
        Config $subject,
        $result,
        DataObject $to
    ) {
        if ($this->_helperData->isEnabled()
        ) {
            $quote = $this->session->getQuote();
            $to->setAmount($quote->getBaseGrandTotal());
            $cart = $this->_cartFactory->create(['salesModel' => $quote]);
            $to->setPaypalCart($cart);
        }
    }
}
