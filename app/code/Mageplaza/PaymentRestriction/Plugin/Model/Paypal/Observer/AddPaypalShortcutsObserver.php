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

namespace Mageplaza\PaymentRestriction\Plugin\Model\Paypal\Observer;

use Exception;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Paypal\Observer\AddPaypalShortcutsObserver as AddPaypalShortcutsObserverPlugin;
use Mageplaza\PaymentRestriction\Model\Config\Source\Action;
use Mageplaza\PaymentRestriction\Plugin\PaypalShortcutsPlugin;

/**
 * Class AddPaypalShortcutsObserver
 * @package Mageplaza\PaymentRestriction\Plugin\Model\Paypal\Observer
 */
class AddPaypalShortcutsObserver extends PaypalShortcutsPlugin
{
    /**
     * @var bool|Rule
     */
    protected $appliedRule;

    /**
     * @var bool
     */
    protected $ruleActive = false;

    /**
     * @param AddPaypalShortcutsObserverPlugin $subject
     * @param callable $proceed
     * @param EventObserver $observer
     *
     * @throws Exception
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundExecute(AddPaypalShortcutsObserverPlugin $subject, callable $proceed, EventObserver $observer)
    {
        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $blocks          = [
            'Magento\Paypal\Block\Express\Shortcut'        => PaypalConfig::METHOD_WPP_EXPRESS,
            'Magento\Paypal\Block\Bml\Shortcut'            => PaypalConfig::METHOD_WPP_EXPRESS,
            'Magento\Paypal\Block\WpsExpress\Shortcut'     => PaypalConfig::METHOD_WPS_EXPRESS,
            'Magento\Paypal\Block\WpsBml\Shortcut'         => PaypalConfig::METHOD_WPS_EXPRESS,
            'Magento\Paypal\Block\PayflowExpress\Shortcut' => PaypalConfig::METHOD_WPP_PE_EXPRESS,
            'Magento\Paypal\Block\Payflow\Bml\Shortcut'    => PaypalConfig::METHOD_WPP_PE_EXPRESS
        ];
        if ($this->_helperData->versionCompare('2.3.2')) {
            $blocks ['Magento\Paypal\Block\Express\InContext\Minicart\SmartButton'] = PaypalConfig::METHOD_WPS_EXPRESS;
            $blocks ['Magento\Paypal\Block\Express\InContext\SmartButton']          = PaypalConfig::METHOD_WPS_EXPRESS;
        } else {
            $blocks ['Magento\Paypal\Block\Express\InContext\Minicart\Button'] = PaypalConfig::METHOD_WPS_EXPRESS;
        }

        if ($this->_helperData->isEnabled()) {
            $quote = $this->_checkoutSession->getQuote();
            if ($quote
                && ($cartId = $quote->getId())
                && $this->_request->getFullActionName() !== 'catalog_product_view') {
                $this->_collectTotals($cartId);
                $appliedSaleRuleIds = $quote->getShippingAddress()->getAppliedRuleIds();
                $appliedSaleRuleIds = explode(',', $appliedSaleRuleIds);
                $shippingAddress    = $quote->getShippingAddress();

                $this->appliedRule = $this->_helperData->checkApplyRule($shippingAddress, $appliedSaleRuleIds);

                if ($this->appliedRule) {
                    $pickedPaymentMethods = $this->appliedRule->getPaymentMethods();
                    $pickedPaymentMethods = explode(',', $pickedPaymentMethods);
                    $activeMethods        = $this->_helperData->getActiveMethods();
                    $activePaypalMethods  = $activeMethods['paypal'];
                    if ($this->appliedRule->getAction() == Action::SHOW) {
                        if (!in_array(PaypalConfig::METHOD_WPP_EXPRESS, $pickedPaymentMethods, true)) {
                            unset($blocks['Magento\Paypal\Block\Bml\Shortcut']);
                            unset($blocks['Magento\Paypal\Block\WpsBml\Shortcut']);
                        }
                        if (!array_intersect($activePaypalMethods, $pickedPaymentMethods)) {
                            unset($blocks['Magento\Paypal\Block\Express\InContext\Minicart\Button']);
                            unset($blocks['Magento\Paypal\Block\Express\Shortcut']);
                            unset($blocks['Magento\Paypal\Block\WpsExpress\Shortcut']);
                        }
                    } else {
                        if (in_array(PaypalConfig::METHOD_WPP_EXPRESS, $pickedPaymentMethods, true)) {
                            unset($blocks['Magento\Paypal\Block\Bml\Shortcut']);
                            unset($blocks['Magento\Paypal\Block\WpsBml\Shortcut']);
                        }
                        if (!array_diff($activePaypalMethods, $pickedPaymentMethods)) {
                            unset($blocks['Magento\Paypal\Block\Express\InContext\Minicart\Button']);
                            unset($blocks['Magento\Paypal\Block\Express\Shortcut']);
                            unset($blocks['Magento\Paypal\Block\WpsExpress\Shortcut']);
                        }
                    }
                }
            }
        }

        foreach ($blocks as $blockInstanceName => $paymentMethodCode) {
            if (!$this->paypalConfig->isMethodAvailable($paymentMethodCode)) {
                continue;
            }

            $params = [
                'shortcutValidator' => $this->shortcutFactory->create($observer->getEvent()->getCheckoutSession()),
            ];
            if (!in_array('Bml', explode('\\', $blockInstanceName), true)) {
                $params['checkoutSession'] = $observer->getEvent()->getCheckoutSession();
            }

            // we believe it's \Magento\Framework\View\Element\Template
            $shortcut = $shortcutButtons->getLayout()->createBlock(
                $blockInstanceName,
                '',
                $params
            );
            $shortcut->setIsInCatalogProduct(
                $observer->getEvent()->getIsCatalogProduct()
            )->setShowOrPosition(
                $observer->getEvent()->getOrPosition()
            );
            $shortcutButtons->addShortcut($shortcut);
        }
    }
}
