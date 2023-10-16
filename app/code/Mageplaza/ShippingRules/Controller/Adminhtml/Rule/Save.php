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

namespace Mageplaza\ShippingRules\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\ShippingRules\Controller\Adminhtml\Rule;
use Mageplaza\ShippingRules\Helper\Data;
use Mageplaza\ShippingRules\Model\RuleFactory;

/**
 * Class Save
 * @package Mageplaza\ShippingRules\Controller\Adminhtml\Rule
 */
class Save extends Rule
{
    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
    public $jsHelper;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param RuleFactory $ruleFactory
     * @param Js $jsHelper
     * @param DateTime $date
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RuleFactory $ruleFactory,
        Js $jsHelper,
        DateTime $date,
        Data $helperData
    ) {
        $this->jsHelper = $jsHelper;
        $this->date = $date;
        $this->_helperData = $helperData;

        parent::__construct($ruleFactory, $registry, $context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            /** @var \Mageplaza\ShippingRules\Model\Rule $rule */
            $rule = $this->initRule();
            $this->prepareData($rule, $data['rule']);

            /** get rule conditions */
            $rule->loadPost($data['rule']);
            $this->_eventManager->dispatch('mageplaza_shippingrules_rule_prepare_save', ['post' => $rule, 'request' => $this->getRequest()]);

            try {
                $rule->save();

                $this->messageManager->addSuccess(__('The rule has been saved.'));
                $this->_getSession()->setData('mageplaza_shippingrules_rule_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mpshippingrules/*/edit', ['id' => $rule->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('mpshippingrules/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Rule.'));
            }

            $this->_getSession()->setData('mageplaza_shippingrules_rule_data', $data);

            $resultRedirect->setPath('mpshippingrules/*/edit', ['id' => $rule->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mpshippingrules/*/');

        return $resultRedirect;
    }

    /**
     * @param $rule
     * @param array $data
     *
     * @return $this
     */
    protected function prepareData($rule, $data = [])
    {
        if ($rule->getCreatedAt() == null) {
            $data['created_at'] = $this->date->date();
        }
        $data['started_at'] = ($data['started_at_name'] == '') ? $this->date->date('m/d/Y') : $data['started_at_name'];
        $data['updated_at'] = $this->date->date();

        if (isset($data['order_scope_name'])) {
            if (isset($data['order_scope_name']['fee'])) {
                $data['order_scope_name']['fee'] = ((float)$data['order_scope_name']['fee'] > 0)
                    ? round((float)$data['order_scope_name']['fee'], 2)
                    : '';
            }
            $data['order_scope'] = $this->_helperData->jsonEncode($data['order_scope_name']);
        }
        if (isset($data['cart_scope_name'])) {
            if (isset($data['cart_scope_name']['fee'])) {
                $data['cart_scope_name']['fee'] = ((float)$data['cart_scope_name']['fee'] > 0)
                    ? round((float)$data['cart_scope_name']['fee'], 2)
                    : '';
            }
            $data['cart_scope'] = $this->_helperData->jsonEncode($data['cart_scope_name']);
        }
        if (isset($data['schedule_name'])) {
            $data['schedule'] = $this->_helperData->jsonEncode($data['schedule_name']);
        }
        if (isset($data['min_total_fee_name'])) {
            if (!is_numeric($data['min_total_fee_name'])) {
                $data['min_total_fee'] = null;
            } else {
                $data['min_total_fee'] = ((float)$data['min_total_fee_name'] >= 0)
                    ? round((float)$data['min_total_fee_name'], 2)
                    : null;
            }
        }
        if (isset($data['min_fee_change_name'])) {
            if (!is_numeric($data['min_fee_change_name'])) {
                $data['min_fee_change'] = null;
            } else {
                $data['min_fee_change'] = ((float)$data['min_fee_change_name'] >= 0)
                    ? round((float)$data['min_fee_change_name'], 2)
                    : null;
            }
        }
        if (isset($data['max_fee_change_name'])) {
            if (!is_numeric($data['max_fee_change_name'])) {
                $data['max_fee_change'] = null;
            } else {
                $data['max_fee_change'] = ((float)$data['max_fee_change_name'] >= 0)
                    ? round((float)$data['max_fee_change_name'], 2)
                    : null;
            }
        }
        if (isset($data['max_total_fee_name'])) {
            if (!is_numeric($data['max_total_fee_name'])) {
                $data['max_total_fee'] = null;
            } else {
                $data['max_total_fee'] = ((float)$data['max_total_fee_name'] >= 0)
                    ? round((float)$data['max_total_fee_name'], 2)
                    : null;
            }
        }
        if (!isset($data['shipping_methods'])) {
            $data['shipping_methods'] = [];
        }
        $rule->addData($data);

        return $this;
    }
}
