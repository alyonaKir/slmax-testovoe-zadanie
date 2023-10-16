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

namespace Mageplaza\ShippingRules\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as MagentoCondition;
use Magento\Rule\Model\Condition\AbstractCondition;
use Mageplaza\ShippingRules\Model\Config\Source\SaleRule;

/**
 * Class Conditions
 * @package Mageplaza\ShippingRules\Block\Adminhtml\Rule\Edit\Tab
 */
class Conditions extends Generic implements TabInterface
{
    /**
     * @var MagentoCondition
     */
    protected $_conditions;

    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var SaleRule
     */
    protected $_saleRuleOptions;

    /**
     * Conditions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param MagentoCondition $conditions
     * @param Fieldset $rendererFieldset
     * @param SaleRule $saleRule
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        MagentoCondition $conditions,
        Fieldset $rendererFieldset,
        SaleRule $saleRule,
        array $data = []
    ) {
        $this->_conditions = $conditions;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_saleRuleOptions = $saleRule;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\ShippingRules\Model\Rule $rule */
        $rule = $this->_coreRegistry->registry('mageplaza_shippingrules_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $formName = 'rule_conditions_fieldset';
        $newChildUrl = $this->getUrl(
            'mpshippingrules/condition/newConditionHtml/form/' . $formName
        );
        $renderer = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl);

        $fieldset = $form->addFieldset('conditions_fieldset', [
                'legend' => __('Apply the rule only if the following conditions are met (leave blank for all products).'),
            ])->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule($rule)->setRenderer($this->_conditions);

        $cartRuleFieldset = $form->addFieldset('cartRule_fieldset', [
            'legend' => __('Apply the rule depending on Cart Price Rules (This will overide the condition above).'),
            'class'  => 'fieldset-wide'
        ]);

        $cartRuleFieldset->addField('sale_rules_active', 'multiselect', [
            'name'   => 'sale_rules_active',
            'label'  => __('Active if these Cart Price Rules are applied'),
            'title'  => __('Active if these Cart Price Rules are applied'),
            'values' => $this->_saleRuleOptions->toOptionArray()
        ]);
        if (!$rule->hasData('sale_rules_active')) {
            $rule->setSaleRulesActive(0);
        }

        $cartRuleFieldset->addField('sale_rules_inactive', 'multiselect', [
            'name'   => 'sale_rules_inactive',
            'label'  => __('Inactive if these Cart Price Rules are applied'),
            'title'  => __('Inactive if these Cart Price Rules are applied'),
            'values' => $this->_saleRuleOptions->toOptionArray(),
            'note'   => __('If a rule is selected in both fields, this field will be applied.')
        ]);
        if (!$rule->hasData('sale_rules_inactive')) {
            $rule->setSaleRulesInactive(0);
        }

        $form->addValues($rule->getData());
        $rule->getConditions()->setJsFormObject($formName);
        $this->setConditionFormName($rule->getConditions(), $formName);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName(AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Conditions');
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
