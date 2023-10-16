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

namespace Mageplaza\PaymentRestriction\Plugin\Adminhtml\Block\Form;

use Exception;
use Magento\Payment\Block\Form\Container as ContainerPlugin;
use Magento\Quote\Model\Quote;
use Mageplaza\PaymentRestriction\Model\Config\Source\Action;
use Mageplaza\PaymentRestriction\Plugin\PaymentRestrictionPlugin;

/**
 * Class Container
 * @package Mageplaza\PaymentRestriction\Plugin\Adminhtml\Block\Form
 */
class Container extends PaymentRestrictionPlugin
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
     * @param ContainerPlugin $subject
     * @param $methods
     *
     * @return array
     * @throws Exception
     */
    public function afterGetMethods(
        ContainerPlugin $subject,
        $methods
    ) {
        $newMethods = $methods;
        /** @var Quote $quote */
        $quote = $this->_quoteSession->getQuote();
        if ($quote) {
            if ($this->_helperData->isEnabled($quote->getStore()->getId())) {
                $newMethods = [];

                $shippingAddress = $quote->getShippingAddress();
                $shippingAddress->setCollectShippingRates(true);
                $appliedSaleRuleIds = $this->_backendSession->getData('mp_paymentrestriction_applied_rule_ids');
                if (!is_array($appliedSaleRuleIds)) {
                    $appliedSaleRuleIds = [];
                }
                $currentWebsiteId = $quote->getStore()->getWebsiteId();

                $this->appliedRule = $this->_helperData->checkApplyRule(
                    $shippingAddress,
                    $appliedSaleRuleIds,
                    $quote->getCustomerGroupId(),
                    $currentWebsiteId,
                    $quote->getStoreId()
                );
                if ($this->appliedRule) {
                    $pickedPaymentMethods = $this->appliedRule->getPaymentMethods();
                    $pickedPaymentMethods = explode(',', $pickedPaymentMethods);

                    foreach ($methods as $paymentCode => $paymentModel) {
                        $options[$paymentCode] = [
                            'label' => $paymentModel->getTitle(),
                            'value' => $paymentModel->getCode()
                        ];
                        if ($this->appliedRule->getAction() === Action::SHOW) {
                            if (in_array($paymentModel->getCode(), $pickedPaymentMethods, true)) {
                                $newMethods[] = $paymentModel;
                            }
                        } elseif (!in_array($paymentModel->getCode(), $pickedPaymentMethods, true)) {
                            $newMethods[] = $paymentModel;
                        }
                    }
                } else {
                    $newMethods = $methods;
                }
            }
        }

        return $newMethods;
    }
}
