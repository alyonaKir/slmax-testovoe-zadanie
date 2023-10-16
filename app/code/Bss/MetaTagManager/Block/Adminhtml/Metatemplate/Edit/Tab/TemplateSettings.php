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

/**
 * Class TemplateSettings
 *
 * @package Bss\MetaTagManager\Block\Adminhtml\Metatemplate\Edit\Tab
 */
class TemplateSettings extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('bss_metatagmanager_meta_template');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Template Settings')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        /* @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $textAreaRenderer */
        $textAreaRenderer = $this->getLayout()->createBlock(
            'Bss\MetaTagManager\Block\Adminhtml\Metatemplate\Edit\Tab\TemplateSettings\Textarea'
        );

        $metaOptions = [
            'meta_title'        => 'Meta Title',
            'meta_description'  => 'Meta Description',
            'meta_keyword'      => 'Meta Keyword',
            'url_key'           => 'URL Key',
            'short_description' => 'Short Description',
            'full_description'  => 'Description'
        ];

        $data = [
            'add_widgets' => false,
            'add_variables' => false,
        ];
        $configTyni = $this->wysiwygConfig->getConfig($data);

        foreach ($metaOptions as $value => $label) {
            if ($value == 'full_description' || $value == 'short_description') {
                $optionField = $fieldset->addField(
                    $value,
                    'editor',
                    [
                    'name' => $value,
                    'label' => __($label),
                    'title' => __($label),
                    'config' => $configTyni,
                    ]
                );
            } else {
                $optionField = $fieldset->addField(
                    $value,
                    'editor',
                    [
                    'name' => $value,
                    'label' => __($label),
                    'title' => __($label),
                    ]
                );
            }
            $optionField->setRenderer($textAreaRenderer);
        }

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
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
        return __('Template Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Template Settings');
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
