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

namespace Mageplaza\ShippingRestriction\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\ShippingRestriction\Controller\Adminhtml\Rule;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule as RuleResource;
use Mageplaza\ShippingRestriction\Model\RuleFactory;

/**
 * Class Edit
 * @package Mageplaza\ShippingRestriction\Controller\Adminhtml\Rule
 */
class Edit extends Rule
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param RuleFactory $ruleFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param RuleResource $ruleResource
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        RuleFactory $ruleFactory,
        Registry $coreRegistry,
        Context $context,
        RuleResource $ruleResource,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct(
            $ruleFactory,
            $coreRegistry,
            $context,
            $ruleResource
        );
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        /** @var \Mageplaza\ShippingRestriction\Model\Rule $rule */
        $rule = $this->initRule();
        if (!$rule) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*');

            return $resultRedirect;
        }

        $data = $this->_session->getData('mageplaza_shippingrestriction_rule_data', true);
        if (!empty($data)) {
            $rule->setData($data);
        }

        $rule->getConditions()->setFormName('rule_conditions_fieldset');
        $rule->getConditions()->setJsFormObject(
            $rule->getConditionsFieldSetId($rule->getConditions()->getFormName())
        );

        $this->coreRegistry->register('mageplaza_shippingrestriction_rule', $rule);

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_ShippingRestriction::rule');
        $resultPage->getConfig()->getTitle()->set(__('Rules'));

        $title = $rule->getId() ? $rule->getName() : __('New Rule');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
