<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Block\Adminhtml\Label\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Bss\ProductLabel\Block\Adminhtml\Label\Edit\Renderer\ImageCustomizeRenderer;

/**
 * Class Form
 * @package Bss\ProductLabel\Block\Adminhtml\Label\Edit
 */
class Form extends Generic
{
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    private $rendererFieldset;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    private $groupOptions;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param GroupCollection $groupOptions
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Rule\Block\Conditions $conditions,
        GroupCollection $groupOptions,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        $this->groupOptions = $groupOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('productlabel_label');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' =>
                [
                    'id'    => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype'=>'multipart/form-data'
                ]
            ]
        );
        $form->setHtmlIdPrefix('label_');
        $form->setFieldNameSuffix('label');
        $form->setUseContainer(true);

        $fieldset = $form->addFieldset('base_fieldset', [ 'legend' => __('General') ]);

        $this->prepareBaseFieldSet($fieldset, $model);

        /**
         * Prepare for conditions fieldset
         */
        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('productlabel/label/newConditionHtml/form/label_conditions_fieldset')
        );

        $fieldset2 = $form->addFieldset(
            'conditions_fieldset',
            [ 'legend' => __('Apply label to product only if the following conditions are met') ]
        )->setRenderer(
            $renderer
        );

        $fieldset2->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->conditions
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare Base Field Set for form
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Bss\ProductLabel\Model\Label $model
     */
    private function prepareBaseFieldSet(&$fieldset, $model)
    {
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField('image_data', 'hidden', ['name' => 'image_data']);
        $fieldset->addField(
            'name',
            'text',
            [
                'name'        => 'name',
                'label'    => __('Name'),
                'required'     => true
            ]
        );
        $fieldset->addField(
            'active',
            'select',
            [
                'name'      => 'active',
                'label'     => __('Enable'),
                'values' => [
                    ['value' => 1, 'label' => __('Yes')],
                    ['value' => 0, 'label' => __('No')],
                ]
            ]
        );
        $fieldset->addField('image', 'image', ['name' => 'image', 'label' => __('Image'), 'title' => __('Image')]);
        $fieldset->addType('imageCustomize', ImageCustomizeRenderer::class);
        $fieldset->addField('imageCustomize', 'imageCustomize', ['name' => 'imageCustomize', 'label' => '']);
        $fieldset->addField(
            'priority',
            'text',
            [
                'name'        => 'priority',
                'label'    => __('Priority'),
                'required'     => true,
                'class' => 'validate-zero-or-greater'
            ]
        );
        $fieldset->addField(
            'apply_outofstock_product',
            'select',
            [
                'name'        => 'apply_outofstock_product',
                'label'    => __('Is Out-of-stock Product Label?'),
                'values' => [
                    ['value' => 1, 'label' => __('Yes')],
                    ['value' => 0, 'label' => __('No')],
                ]
            ]
        );
        $fieldset->addField(
            'valid_start_date',
            'date',
            [
                'name' => 'valid_start_date',
                'label' => __('Valid Start Date'),
                'date_format' => 'yyyy-MM-dd',
                'time_format' => 'hh:mm a',
                'note' => __('Leave blank for apply unlimited day')
            ]
        );
        $fieldset->addField(
            'valid_end_date',
            'date',
            [
                'name' => 'valid_end_date',
                'label' => __('Valid End Date'),
                'date_format' => 'yyyy-MM-dd',
                'time_format' => 'hh:mm a',
                'note' => __('Leave blank for apply unlimited day')
            ]
        );
        $fieldset->addField(
            'store_views',
            'multiselect',
            [
                'name' => 'store_views[]',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'values' => $this->getStoreData(),
                'note' => __('Skip to apply label to all stores')
            ]
        )->setCanBeEmpty(true);
        $fieldset->addField(
            'customer_groups',
            'multiselect',
            [
                'name' => 'customer_groups[]',
                'label' => __('Customer Group'),
                'title' => __('Customer Group'),
                'values' => $this->groupOptions->toOptionArray(),
                'note' => __('Skip to apply label to all groups')
            ]
        )->setCanBeEmpty(true);
    }

    /**
     * @return array
     */
    private function getStoreData()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $key => $value) {
            $options[] = ['label' => $value['name'] . ' - ' . $value['code'], 'value' => $key];
        }
        return $options;
    }
}
