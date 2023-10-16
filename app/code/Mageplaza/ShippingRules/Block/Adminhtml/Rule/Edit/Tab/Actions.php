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
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Actions as MagentoAction;
use Magento\Rule\Model\Condition\AbstractCondition;
use Mageplaza\ShippingRules\Helper\Data;
use Mageplaza\ShippingRules\Model\Config\Source\CalculationFee;
use Mageplaza\ShippingRules\Model\Config\Source\CartScope\Type as CartScopeType;
use Mageplaza\ShippingRules\Model\Config\Source\ExtraFee;
use Mageplaza\ShippingRules\Model\Config\Source\OrderScope\Type as OrderScopeType;

/**
 * Class Actions
 * @package Mageplaza\ShippingRules\Block\Adminhtml\Comment\Edit\Tab
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var MagentoAction
     */
    protected $_magentoAction;

    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var CalculationFee
     */
    protected $_calculationFee;

    /**
     * @var OrderScopeType
     */
    protected $_orderScopeType;

    /**
     * @var CartScopeType
     */
    protected $_cartScopeType;

    /**
     * @var ExtraFee
     */
    protected $_extraFee;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param FieldFactory $fieldFactory
     * @param MagentoAction $magentoAction
     * @param Fieldset $rendererFieldset
     * @param Yesno $yesno
     * @param CalculationFee $calculationFee
     * @param OrderScopeType $orderScopeType
     * @param CartScopeType $cartScopeType
     * @param ExtraFee $extraFee
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        FieldFactory $fieldFactory,
        MagentoAction $magentoAction,
        Fieldset $rendererFieldset,
        Yesno $yesno,
        CalculationFee $calculationFee,
        OrderScopeType $orderScopeType,
        CartScopeType $cartScopeType,
        ExtraFee $extraFee,
        Data $helperData,
        array $data = []
    ) {
        $this->_fieldFactory = $fieldFactory;
        $this->_magentoAction = $magentoAction;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_yesNo = $yesno;
        $this->_calculationFee = $calculationFee;
        $this->_orderScopeType = $orderScopeType;
        $this->_cartScopeType = $cartScopeType;
        $this->_extraFee = $extraFee;
        $this->_helperData = $helperData;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $rule = $this->_coreRegistry->registry('mageplaza_shippingrules_rule');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Calculation Shipping Fee'), 'class' => 'fieldset-wide']
        );

        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $applyFeeField = $fieldset->addField('apply_fee', 'select', [
            'name'   => 'apply_fee',
            'label'  => __('How to apply fee'),
            'title'  => __('How to apply fee'),
            'values' => $this->_calculationFee->toOptionArray()
        ]);
        if (!$rule->hasData('apply_fee')) {
            $rule->setStatus(1);
        }

        if ($rule->getMinFeeChange()) {
            $rule->setMinFeeChange(floatval($rule->getMinFeeChange()));
        } elseif ($rule->getMinFeeChange() == 0) {
            $rule->setMinFeeChange('');
        }
        $minFeeChangeField = $fieldset->addField('min_fee_change', 'text', [
            'name'  => 'min_fee_change_name',
            'label' => __('Minimum fee change'),
            'title' => __('Minimum fee change')
        ]);

        if ($rule->getMaxFeeChange()) {
            $rule->setMaxFeeChange(floatval($rule->getMaxFeeChange()));
        } elseif ($rule->getMaxFeeChange() == 0) {
            $rule->setMaxFeeChange('');
        }
        $maxFeeChangeField = $fieldset->addField('max_fee_change', 'text', [
            'name'  => 'max_fee_change_name',
            'label' => __('Maximum fee change'),
            'title' => __('Maximum fee change')
        ]);

        if ($rule->getMinTotalFee()) {
            $rule->setMinTotalFee(floatval($rule->getMinTotalFee()));
        } elseif ($rule->getMinTotalFee() == 0) {
            $rule->setMinTotalFee('');
        }
        $fieldset->addField('min_total_fee', 'text', [
            'name'  => 'min_total_fee_name',
            'label' => __('Minimum of Total Shipping Fee'),
            'title' => __('Minimum of Total Shipping Fee')
        ]);

        if ($rule->getMaxTotalFee()) {
            $rule->setMaxTotalFee(floatval($rule->getMaxTotalFee()));
        } elseif ($rule->getMaxTotalFee() == 0) {
            $rule->setMaxTotalFee('');
        }
        $fieldset->addField('max_total_fee', 'text', [
            'name'  => 'max_total_fee_name',
            'label' => __('Maximum of Total Shipping Fee'),
            'title' => __('Maximum of Total Shipping Fee')
        ]);

        $orderScopeFieldset = $form->addFieldset('order_scope_fieldset', [
            'legend' => __('Order Scope'),
            'class'  => 'fieldset-wide'
        ]);

        $orderScopeTypeField = $orderScopeFieldset->addField('order_scope_type', 'select', [
            'name'   => 'order_scope_name[type]',
            'label'  => __('Type'),
            'title'  => __('Type'),
            'values' => $this->_orderScopeType->toOptionArray()
        ]);
        if (!$rule->hasData('order_scope')) {
            $rule->setData('order_scope_type', OrderScopeType::PERCENTAGE_OF_CART_TOTAL);
        } else {
            $orderScopeData = $this->_helperData->jsonDecode($rule->getData('order_scope'));
            if (isset($orderScopeData['type'])) {
                $rule->setData('order_scope_type', $orderScopeData['type']);
            }
            if (isset($orderScopeData['fee'])) {
                $rule->setData('order_scope_fee', $orderScopeData['fee']);
            }
            if (isset($orderScopeData['extra'])) {
                $rule->setData('order_scope_extra', $orderScopeData['extra']);
            }
        }

        $orderScopeFeeField = $orderScopeFieldset->addField('order_scope_fee', 'text', [
            'name'     => 'order_scope_name[fee]',
            'required' => true,
            'class'    => 'validate-number validate-greater-than-zero',
            'label'    => __('Fee Amount'),
            'title'    => __('Fee Amount')
        ]);

        $orderScopeExtraField = $orderScopeFieldset->addField('order_scope_extra', 'multiselect', [
            'name'   => 'order_scope_name[extra]',
            'label'  => __('Cart Total includes'),
            'title'  => __('Cart Total includes'),
            'values' => $this->_extraFee->toOptionArray()
        ]);

        $cartScopeFieldset = $form->addFieldset('cart_scope_fieldset', [
            'legend' => __('Cart Items Scope'),
            'class'  => 'fieldset-wide'
        ]);

        $cartScopeTypeField = $cartScopeFieldset->addField('cart_scope_type', 'select', [
            'name'   => 'cart_scope_name[type]',
            'label'  => __('Type'),
            'title'  => __('Type'),
            'values' => $this->_cartScopeType->toOptionArray()
        ]);
        if (!$rule->hasData('cart_scope')) {
            $rule->setData('cart_scope_type', CartScopeType::PERCENTAGE_OF_ITEM_PRICE);
        } else {
            $cartScopeData = $this->_helperData->jsonDecode($rule->getData('cart_scope'));
            if (isset($cartScopeData['type'])) {
                $rule->setData('cart_scope_type', $cartScopeData['type']);
            }
            if (isset($cartScopeData['fee'])) {
                $rule->setData('cart_scope_fee', $cartScopeData['fee']);
            }
            if (isset($cartScopeData['extra'])) {
                $rule->setData('cart_scope_extra', $cartScopeData['extra']);
            }
        }

        $cartScopeFeeField = $cartScopeFieldset->addField('cart_scope_fee', 'text', [
            'name'     => 'cart_scope_name[fee]',
            'required' => true,
            'class'    => 'validate-number validate-greater-than-zero',
            'label'    => __('Fee Amount'),
            'title'    => __('Fee Amount')
        ]);

        $cartScopeExtraField = $cartScopeFieldset->addField('cart_scope_extra', 'multiselect', [
            'name'   => 'cart_scope_name[extra]',
            'label'  => __('Item price includes'),
            'title'  => __('Item price includes'),
            'values' => $this->_extraFee->toOptionArray()
        ]);

        $formName = 'rule_actions_fieldset';
        $actionsFieldSetId = $rule->getActionsFieldSetId($formName);
        $newChildUrl = $this->getUrl(
            'mpshippingrules/condition/newActionHtml/form/' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        $renderer = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($actionsFieldSetId);

        $actionFieldset = $form->addFieldset('actions_fieldset', [
                'legend' => __('Apply the rule only to cart items matching the following conditions (leave blank for all items).'),
            ])->setRenderer($renderer);

        $actionFieldset->addField(
            'actions',
            'text',
            ['name' => 'actions', 'label' => __('Actions'), 'title' => __('Actions')]
        )->setRule($rule)->setRenderer($this->_magentoAction);

        $actionFieldset->addField('apply_free_item', 'select', [
            'name'   => 'apply_free_item',
            'label'  => __('Apply for free shipping items'),
            'title'  => __('Apply for free shipping items'),
            'values' => $this->_yesNo->toOptionArray(),
            'note'   => __('If <b>No</b>, the rule will not be applied for products with free shipping.')
        ]);

        $form->addValues($rule->getData());
        $rule->getConditions()->setJsFormObject($formName);
        $this->setActionFormName($rule->getActions(), $formName);

        $calRefField = $this->_fieldFactory->create(['fieldData' => ['value' => '2,3', 'separator' => ','], 'fieldPrefix' => '']);
        $orderRefField = $this->_fieldFactory->create(['fieldData' => ['value' => '1,2,3', 'separator' => ','], 'fieldPrefix' => '']);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap($applyFeeField->getHtmlId(), $applyFeeField->getName())
                ->addFieldMap($orderScopeTypeField->getHtmlId(), $orderScopeTypeField->getName())
                ->addFieldMap($orderScopeFeeField->getHtmlId(), $orderScopeFeeField->getName())
                ->addFieldMap($orderScopeExtraField->getHtmlId(), $orderScopeExtraField->getName())
                ->addFieldMap($cartScopeTypeField->getHtmlId(), $cartScopeTypeField->getName())
                ->addFieldMap($cartScopeFeeField->getHtmlId(), $cartScopeFeeField->getName())
                ->addFieldMap($cartScopeExtraField->getHtmlId(), $cartScopeExtraField->getName())
                ->addFieldMap($minFeeChangeField->getHtmlId(), $minFeeChangeField->getName())
                ->addFieldMap($maxFeeChangeField->getHtmlId(), $maxFeeChangeField->getName())
                ->addFieldDependence($minFeeChangeField->getName(), $applyFeeField->getName(), $calRefField)
                ->addFieldDependence($maxFeeChangeField->getName(), $applyFeeField->getName(), $calRefField)
                ->addFieldDependence($orderScopeExtraField->getName(), $orderScopeTypeField->getName(), OrderScopeType::PERCENTAGE_OF_CART_TOTAL)
                ->addFieldDependence($orderScopeFeeField->getName(), $orderScopeTypeField->getName(), $orderRefField)
                ->addFieldDependence($cartScopeExtraField->getName(), $cartScopeTypeField->getName(), CartScopeType::PERCENTAGE_OF_ITEM_PRICE)
                ->addFieldDependence($cartScopeFeeField->getName(), $cartScopeTypeField->getName(), $orderRefField)
        );
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of form name to action and its actions.
     *
     * @param AbstractCondition $actions
     * @param string $formName
     *
     * @return void
     */
    private function setActionFormName(AbstractCondition $actions, $formName)
    {
        $actions->setFormName($formName);
        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $condition) {
                $this->setActionFormName($condition, $formName);
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
