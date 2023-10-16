<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Block\Adminhtml\Metatemplate\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;

/**
 * Class General
 *
 * @package Bss\MetaTagManager\Block\Adminhtml\Metatemplate\Edit\Tab
 */
class General extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $categoryTemplate;
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    private $rendererFieldset;
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;
    /**
     * @var FieldFactory
     */
    private $fileldFactory;

    /**
     * General constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Bss\MetaTagManager\Model\Config\Category $categoryTemplate
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Bss\MetaTagManager\Model\Config\Category $categoryTemplate,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Rule\Block\Conditions $conditions,
        FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->systemStore = $systemStore;
        $this->conditions = $conditions;
        $this->categoryTemplate = $categoryTemplate;
        $this->fileldFactory = $fieldFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $modelData = $this->_coreRegistry->registry('bss_metatagmanager_meta_template');
        $isElementDisabled = false;

        $attributeObject = $this->_coreRegistry->registry('entity_attribute');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        //General Fieldset
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);
        if ($modelData->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset = $this->setFieldConditionMore($fieldset);

        $this->setFieldMore($fieldset);
        if (!$modelData->getId()) {
            $modelData->setData('status', $isElementDisabled ? '0' : '1');
        }

        //Product Conditions Fieldset
        $formName = 'catalog_rule_form';

        $conditionsFieldSetId = $modelData->getConditionsFieldSetId($formName);
        $modelData->getConditions()->setJsFormObject($conditionsFieldSetId);

        $newChildUrl = $this->getUrl(
            'catalog_rule/promo_catalog/newConditionHtml/form/' . $modelData->getConditionsFieldSetId($formName),
            ['form_namespace' => $formName]
        );
        $renderer = $this->rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($modelData->getConditionsFieldSetId($formName));

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            [
                'legend' => __(
                    'Product Conditions'
                )
            ]
        )->setRenderer(
            $renderer
        );
        //Category Conditions Fieldset
        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $modelData
        )->setRenderer(
            $this->conditions
        );

        $fieldset = $form->addFieldset('category_conditions_fieldset', ['legend' => __('Category Choose')]);
        $this->setFieldProductMore($fieldset);

        $refField = $this->fileldFactory->create(
            ['fieldData' => ['value' => 'product', 'separator' => ','], 'fieldPrefix' => '']
        );

        $dependencies = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Form\Element\Dependence'
        )->addFieldMap('meta_type', 'meta_type')
                ->addFieldMap('name', 'name')
                ->addFieldDependence('name', 'meta_type', $refField);

        $this->_eventManager->dispatch('product_attribute_form_build_layer_tab', [
            'form'         => $form,
            'attribute'    => $attributeObject,
            'dependencies' => $dependencies
        ]);

        $this->setChild('form_after', $dependencies);

        $form->setValues($modelData->getData());
        $this->setConditionFormName($modelData->getConditions(), $formName);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param object $fieldset
     * @return mixed
     */
    public function setFieldMore($fieldset)
    {
        $fieldset->addField(
            'priority',
            'text',
            [
                'name' => 'priority',
                'label' => __('Priority'),
                'title' => __('priority'),
            ]
        );
        return $fieldset;
    }

    /**
     * @param object $fieldset
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setFieldConditionMore($fieldset)
    {
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Enable'),
                'title' => __('Enable'),
                'values' => [
                    ['value' => 0, 'label' => __('No')],
                    ['value' => 1, 'label' => __('Yes')]
                ],
                'required' => true
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'meta_type',
            'select',
            [
                'name' => 'meta_type',
                'label' => __('Meta Template Type'),
                'title' => __('Meta Template Type'),
                'values' => [
                    ['value' => 'product', 'label' => __('Product')],
                    ['value' => 'category', 'label' => __('Category')]
                ],
                'required' => true
            ]
        );

        $storeField = $fieldset->addField(
            'store',
            'multiselect',
            [
                'label' => __('Scope'),
                'required' => true,
                'name' => 'store',
                'values' => $this->systemStore->getStoreValuesForForm()
            ]
        );

        $storeRenderer = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
        );
        $storeField->setRenderer($storeRenderer);
        return $fieldset;
    }

    /**
     * @param object $fieldset
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setFieldProductMore($fieldset)
    {
        $fieldset->addField(
            'category',
            'multiselect',
            [
                'label' => __('Categories'),
                'title' => __('Categories'),
                'name' => 'category',
                'required' => false,
                'values' => $this->categoryTemplate->toOptionArray()
            ]
        );

        $fieldset->addField(
            'use_sub',
            'select',
            [
                'name' => 'use_sub',
                'label' => __('Apply for Sub-categories'),
                'title' => __('Apply for Sub-categories'),
                'values' => [
                    ['value' => 0, 'label' => __('No')],
                    ['value' => 1, 'label' => __('Yes')]
                ],
                'required' => false
            ]
        );
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
