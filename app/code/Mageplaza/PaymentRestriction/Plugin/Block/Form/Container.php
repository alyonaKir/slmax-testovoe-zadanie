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

namespace Mageplaza\PaymentRestriction\Plugin\Block\Form;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Block\Form\Container as ContainerPlugin;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Mageplaza\PaymentRestriction\Model\Config\Source\Action;
use Mageplaza\PaymentRestriction\Model\Rule;
use Mageplaza\PaymentRestriction\Plugin\PaymentRestrictionPlugin;

/**
 * Class Container
 * @package Mageplaza\PaymentRestriction\Plugin\Block\Form
 */
class Container extends PaymentRestrictionPlugin
{
    /**
     * @var Rule
     */
    protected $appliedRule;

    /**
     * @param ContainerPlugin $subject
     * @param array $methods
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetMethods(ContainerPlugin $subject, $methods)
    {
        $newMethods = $methods;
        /** @var Quote $quote */
        $quote = $this->_checkoutSession->getQuote();
        if ($quote && $this->_helperData->isEnabled($quote->getStore()->getId())) {
            $newMethods         = [];
            $appliedRules       = [];
            $appliedSaleRuleIds = explode(',', $quote->getAppliedRuleIds());
            $shippingAddresses  = $quote->getAllShippingAddresses();
            $currentWebsiteId   = $quote->getStore()->getWebsiteId();
            /** @var Address $shippingAddress */
            foreach ($shippingAddresses as $shippingAddress) {
                $appliedRules[] = $this->_helperData->checkApplyRule(
                    $shippingAddress,
                    $appliedSaleRuleIds,
                    $quote->getCustomerGroupId(),
                    $currentWebsiteId,
                    $quote->getStoreId()
                );
            }

            foreach ($appliedRules as $key => $appliedRule) {
                if (!$appliedRule) {
                    unset($appliedRules[$key]);
                }
            }

            $appliedRules = array_values($appliedRules);

            if (count($appliedRules)) {
                $this->appliedRule = $appliedRules[0];
                $priority          = $this->appliedRule->getPriority();
                /** @var Rule $appliedRule */
                foreach ($appliedRules as $appliedRule) {
                    if ($appliedRule->getPriority() < $priority) {
                        $this->appliedRule = $appliedRule;
                        $priority          = $appliedRule->getPriority();
                    }
                }
            }

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

        return $newMethods;
    }
}
