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

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Paypal\Helper\Shortcut\Factory;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Mageplaza\PaymentRestriction\Helper\Data as HelperData;

/**
 * Class PaypalShortcutsPlugin
 * @package Mageplaza\PaymentRestriction\Plugin
 */
class PaypalShortcutsPlugin
{
    /**
     * @var Factory
     */
    protected $shortcutFactory;

    /**
     * @var PaypalConfig
     */
    protected $paypalConfig;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

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
     * @var HelperData
     */
    protected $_helperData;

    /**
     * PaypalShortcutsPlugin constructor.
     *
     * @param Registry $coreRegistry
     * @param RequestInterface $request
     * @param TotalsCollector $totalsCollector
     * @param CartRepositoryInterface $cartRepository
     * @param CheckoutSession $checkoutSession
     * @param Factory $shortcutFactory
     * @param PaypalConfig $paypalConfig
     * @param HelperData $helperData
     */
    public function __construct(
        Registry $coreRegistry,
        RequestInterface $request,
        TotalsCollector $totalsCollector,
        CartRepositoryInterface $cartRepository,
        CheckoutSession $checkoutSession,
        Factory $shortcutFactory,
        PaypalConfig $paypalConfig,
        HelperData $helperData
    ) {
        $this->_coreRegistry    = $coreRegistry;
        $this->_request         = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_cartRepository  = $cartRepository;
        $this->_totalsCollector = $totalsCollector;
        $this->shortcutFactory  = $shortcutFactory;
        $this->paypalConfig     = $paypalConfig;
        $this->_helperData      = $helperData;
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
