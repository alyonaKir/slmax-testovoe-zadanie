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

namespace Mageplaza\PaymentRestriction\Plugin\Model\Braintree\Observer;

use Exception;
use Magento\Braintree\Block\Paypal\Button;
use Magento\Braintree\Observer\AddPaypalShortcuts as AddPaypalShortcutsPlugin;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\PaymentRestriction\Model\Config\Source\Action;
use Mageplaza\PaymentRestriction\Plugin\PaypalShortcutsPlugin;

/**
 * Class AddPaypalShortcuts
 * @package Mageplaza\PaymentRestriction\Plugin\Model\Braintree\Observer
 */
class AddPaypalShortcuts extends PaypalShortcutsPlugin
{
    /**
     * Block class
     */
    const PAYPAL_SHORTCUT_BLOCK = Button::class;

    /**
     * @var bool|Rule
     */
    protected $appliedRule;

    /**
     * @var bool
     */
    protected $ruleActive = false;

    /**
     * @param AddPaypalShortcutsPlugin $subject
     * @param callable $proceed
     * @param Observer $observer
     *
     * @throws Exception
     * @throws LocalizedException
     */
    public function aroundExecute(AddPaypalShortcutsPlugin $subject, callable $proceed, Observer $observer)
    {
        if ($this->_helperData->isEnabled()) {
            $quote = $this->_checkoutSession->getQuote();
            if ($quote
                && ($cartId = $quote->getId())
                && $this->_request->getFullActionName() !== 'catalog_product_view'
            ) {
                $this->_collectTotals($cartId);
                $appliedSaleRuleIds = $quote->getShippingAddress()->getAppliedRuleIds();
                $appliedSaleRuleIds = explode(',', $appliedSaleRuleIds);
                $shippingAddress    = $quote->getShippingAddress();

                $this->appliedRule = $this->_helperData->checkApplyRule($shippingAddress, $appliedSaleRuleIds);

                if ($this->appliedRule) {
                    $isShow = ($this->appliedRule->getAction() === Action::SHOW);

                    $pickedPaymentMethods = explode(',', $this->appliedRule->getPaymentMethods());
                    $isBraintreePaypal    = in_array('braintree_paypal', $pickedPaymentMethods, true);

                    if (($isShow && !$isBraintreePaypal) || (!$isShow && $isBraintreePaypal)) {
                        return;
                    }
                }
            }
        }
        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        /** @var Button $shortcut */
        $shortcut = $shortcutButtons->getLayout()->createBlock(self::PAYPAL_SHORTCUT_BLOCK);

        $shortcutButtons->addShortcut($shortcut);
    }
}
