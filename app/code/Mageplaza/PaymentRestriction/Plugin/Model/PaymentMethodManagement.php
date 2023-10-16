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

namespace Mageplaza\PaymentRestriction\Plugin\Model;

use Exception;
use Mageplaza\PaymentRestriction\Model\Rule;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\PaymentMethodManagement as PaymentMethodManagementPlugin;
use Magento\Quote\Model\Quote;
use Mageplaza\PaymentRestriction\Model\Config\Source\Action;
use Mageplaza\PaymentRestriction\Plugin\PaymentRestrictionPlugin;

/**
 * Class PaymentMethodManagement
 * @package Mageplaza\PaymentRestriction\Plugin\Model
 */
class PaymentMethodManagement extends PaymentRestrictionPlugin
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
     * @param PaymentMethodManagementPlugin $subject
     * @param MethodInterface[] $availableMethods
     *
     * @return array
     * @throws Exception
     */
    public function afterGetList(PaymentMethodManagementPlugin $subject, $availableMethods)
    {
        $newAvailableMethods = $availableMethods;
        if ($this->_helperData->isEnabled($this->_storeManagement->getStore()->getId())) {
            $newAvailableMethods = [];
            /** @var Quote $quote */
            $quote = $this->_coreRegistry->registry('mp_paymentrestriction_quote');
            if ((!$quote && $this->_request->getFullActionName() == 'onestepcheckout_index_index') ||
                $this->_checkoutSession->getQuote()->isVirtual()) {
                $quote = $this->_checkoutSession->getQuote();
            }
            if ($quote) {
                $appliedSaleRuleIds = $quote->getShippingAddress()->getAppliedRuleIds();
                $appliedSaleRuleIds = explode(',', $appliedSaleRuleIds);
                $shippingAddress    = $quote->getShippingAddress();

                $this->appliedRule  = $this->_helperData->checkApplyRule($shippingAddress, $appliedSaleRuleIds);

                if ($this->appliedRule) {
                    $pickedPaymentMethods = $this->appliedRule->getPaymentMethods();
                    $pickedPaymentMethods = explode(',', $pickedPaymentMethods);

                    foreach ($availableMethods as $paymentCode => $paymentModel) {
                        $options[$paymentCode] = [
                            'label' => $paymentModel->getTitle(),
                            'value' => $paymentModel->getCode()
                        ];
                        if ($this->appliedRule->getAction() == Action::SHOW) {
                            if (in_array($paymentModel->getCode(), $pickedPaymentMethods)) {
                                $newAvailableMethods[] = $paymentModel;
                            }
                        } else {
                            if (!in_array($paymentModel->getCode(), $pickedPaymentMethods)) {
                                $newAvailableMethods[] = $paymentModel;
                            }
                        }
                    }
                } else {
                    $newAvailableMethods = $availableMethods;
                }
            }
        }

        return $newAvailableMethods;
    }
}
