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

namespace Mageplaza\ShippingRestriction\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\ShippingRestriction\Block\Adminhtml\Rule\Edit\Tab\Renderer\ShippingMethod;
use Mageplaza\ShippingRestriction\Model\Config\Source\Action;
use Mageplaza\ShippingRestriction\Model\Config\Source\Location;
use Mageplaza\ShippingRestriction\Model\Rule;

/**
 * Class Actions
 * @package Mageplaza\ShippingRestriction\Block\Adminhtml\Rule\Edit\Tab
 */
class Actions extends Generic implements TabInterface
{
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
     * @param Action $action
     * @param Location $location
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Action $action,
        Location $location,
        array $data = []
    ) {
        $this->_action   = $action;
        $this->_location = $location;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var Rule $rule */
        $rule = $this->_coreRegistry->registry('mageplaza_shippingrestriction_rule');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Actions'), 'class' => 'fieldset-wide']);

        $fieldset->addField('action', 'select', [
            'name'   => 'action',
            'label'  => __('What To Do'),
            'title'  => __('What To Do'),
            'values' => $this->_action->toOptionArray()
        ]);
        if (!$rule->hasData('action')) {
            $rule->setAction(1);
        }

        $fieldset->addField('shipping_methods', ShippingMethod::class, [
            'name'  => 'shipping_methods',
            'label' => __('Select Shipping Methods'),
            'title' => __('Select Shipping Methods')
        ]);

        $fieldset->addField('location', 'multiselect', [
            'name'   => 'location',
            'label'  => __('Where To Apply'),
            'title'  => __('Where To Apply'),
            'values' => $this->_location->toOptionArray()
        ]);
        if (!$rule->hasData('location')) {
            $rule->setLocation(1);
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
