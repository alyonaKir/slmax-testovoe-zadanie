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

namespace Mageplaza\PaymentRestriction\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\PaymentRestriction\Block\Adminhtml\Rule\Edit\Tab\Renderer\PaymentMethod;
use Mageplaza\PaymentRestriction\Model\Config\Source\Action;
use Mageplaza\PaymentRestriction\Model\Config\Source\Location;
use Mageplaza\PaymentRestriction\Model\Rule;
use Mageplaza\PaymentRestriction\Model\RuleFactory;

/**
 * Class Actions
 * @package Mageplaza\PaymentRestriction\Block\Adminhtml\Rule\Edit\Tab
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var Action
     */
    protected $_action;

    /**
     * @var Location
     */
    protected $_location;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param RuleFactory $ruleFactory
     * @param Action $action
     * @param Location $location
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CustomerRepositoryInterface $customerRepository,
        RuleFactory $ruleFactory,
        Action $action,
        Location $location,
        array $data = []
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_ruleFactory        = $ruleFactory;
        $this->_action             = $action;
        $this->_location           = $location;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Rule $rule */
        $rule = $this->_coreRegistry->registry('mageplaza_paymentrestriction_rule');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Actions'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('payment_methods', PaymentMethod::class, [
            'name'     => 'payment_methods',
            'label'    => __('Select Payment Method(s)'),
            'title'    => __('Select Payment Method(s)'),
            'required' => true
        ]);

        $fieldset->addField('action', 'select', [
            'name'   => 'action',
            'label'  => __('Action'),
            'title'  => __('Action'),
            'values' => $this->_action->toOptionArray()
        ]);
        if (!$rule->hasData('action')) {
            $rule->setAction(Action::SHOW);
        }

        $fieldset->addField('location', 'multiselect', [
            'name'   => 'location',
            'label'  => __('Location'),
            'title'  => __('Location'),
            'values' => $this->_location->toOptionArray()
        ]);
        if (!$rule->hasData('location')) {
            $rule->setLocation(Location::ORDER_BACKEND);
        }

        $form->addValues($rule->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
