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

namespace Mageplaza\PaymentRestriction\Plugin;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\PaymentRestriction\Helper\Data as HelperData;

/**
 * Class PaymentRestrictionPlugin
 * @package Mageplaza\PaymentRestriction\Plugin
 */
class PaymentRestrictionPlugin
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var TotalsCollector
     */
    protected $_totalsCollector;

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
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $_quoteIdMaskFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManagement;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * PaymentRestrictionPlugin constructor.
     *
     * @param Registry $coreRegistry
     * @param TotalsCollector $totalsCollector
     * @param CartRepositoryInterface $cartRepository
     * @param BackendSession $backendSession
     * @param QuoteSession $quoteSession
     * @param CheckoutSession $checkoutSession
     * @param RequestInterface $request
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helperData
     */
    public function __construct(
        Registry $coreRegistry,
        TotalsCollector $totalsCollector,
        CartRepositoryInterface $cartRepository,
        BackendSession $backendSession,
        QuoteSession $quoteSession,
        CheckoutSession $checkoutSession,
        RequestInterface $request,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        StoreManagerInterface $storeManager,
        HelperData $helperData
    ) {
        $this->_coreRegistry       = $coreRegistry;
        $this->_cartRepository     = $cartRepository;
        $this->_totalsCollector    = $totalsCollector;
        $this->_backendSession     = $backendSession;
        $this->_quoteSession       = $quoteSession;
        $this->_checkoutSession    = $checkoutSession;
        $this->_request            = $request;
        $this->_quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->_storeManagement    = $storeManager;
        $this->_helperData         = $helperData;
    }

    /**
     * @param $cartId
     *
     * @throws NoSuchEntityException
     */
    protected function _collectTotals($cartId)
    {
        /** @var Quote $quote */
        $quote           = $this->_cartRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $this->_totalsCollector->collectAddressTotals($quote, $shippingAddress);
    }
}
