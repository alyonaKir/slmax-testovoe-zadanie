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
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Actions;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\AutoRelated\Helper\Data;
use Mageplaza\AutoRelated\Model\Config\Source\Additional;
use Mageplaza\AutoRelated\Model\Config\Source\AddProductTypes;
use Mageplaza\AutoRelated\Model\Config\Source\Direction;
use Mageplaza\AutoRelated\Model\Config\Source\DisplayMode;
use Mageplaza\AutoRelated\Model\Config\Source\ProductNotDisplayed;
use Mageplaza\AutoRelated\Model\Config\Source\Type as RuleType;
use Mageplaza\AutoRelated\Model\DataForm\Element\Text;

/**
 * Class BlockConfig
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Actions
 */
class BlockConfig extends Generic
{
    /**
     * @var Direction
     */
    protected $direction;

    /**
     * @var Additional
     */
    protected $additional;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var AddProductTypes
     */
    protected $addProductTypes;

    /**
     * @var ProductNotDisplayed
     */
    protected $productNotDisplayed;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Yesno
     */
    protected $yesno;

    /**
     * BlockConfig constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Direction $direction
     * @param Additional $additional
     * @param AddProductTypes $addProductTypes
     * @param ProductNotDisplayed $productNotDisplayed
     * @param Data $helperData
     * @param Http $request
     * @param Yesno $yesno
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Direction $direction,
        Additional $additional,
        AddProductTypes $addProductTypes,
        ProductNotDisplayed $productNotDisplayed,
        Data $helperData,
        Http $request,
        Yesno $yesno,
        array $data = []
    ) {
        $this->additional          = $additional;
        $this->direction           = $direction;
        $this->addProductTypes     = $addProductTypes;
        $this->helperData          = $helperData;
        $this->request             = $request;
        $this->productNotDisplayed = $productNotDisplayed;
        $this->yesno               = $yesno;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('autorelated_rule');

        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('block_config_rule_');

        $fieldset     = $form->addFieldset('block_base_fieldset', ['legend' => __('Block Configuration')]);
        $sliderConfig = [
            'slider_width'    => null,
            'slider_height'   => null,
            'show_next_prev'  => 1,
            'show_dots_nav'   => 1,
            'slider_autoplay' => 1
        ];

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
            if ($model->getSliderConfig()) {
                $sliderConfig = $this->helperData->unserialize($model->getSliderConfig());
            }
        }

        $fieldset->addField('block_name', 'text', [
            'name'  => 'block_name',
            'label' => __('Block name'),
            'title' => __('Block name'),
            'note'  => __('Enter the block\'s name. It\'s only visible in the frontend`.')
        ]);

        $layout = $fieldset->addField('product_layout', 'select', [
            'name'    => 'product_layout',
            'label'   => __('Product layout'),
            'title'   => __('Product layout'),
            'options' => [
                '1' => __('Grid'),
                '0' => __('Slider')
            ],
            'note'    => __('Select how products are displayed.')
        ]);

        $fieldset->addField('page_column_layout', Text::class, [
            'name'  => 'page_column_layout',
            'label' => __('Page Column Layout'),
            'title' => __('Page Column Layout'),
            'class' => 'validate-number',
            'max'   => '5',
            'note'  => __('The limit is 5 columns. If left blank or 0, it will default to 4 columns.')
        ]);

        $fieldset->addField('number_product_slider', Text::class, [
            'name'  => 'number_product_slider',
            'label' => __('Number of products on the Slider'),
            'title' => __('Number of products on the Slider'),
            'class' => 'validate-number',
            'max'   => '5',
            'note'  => __('The limit is 5. If empty or 0, will default to 5 items displayed on the slider')
        ]);

        $fieldset->addField('number_product_scrolled', Text::class, [
            'name'  => 'number_product_scrolled',
            'label' => __('Product Displayed When Scrolled'),
            'title' => __('Product Displayed When Scrolled'),
            'class' => 'validate-number',
            'max'   => '5',
            'note'  => __('The limit is 5. If it is empty or 0, after each before/after click on the slider,
            it will replace the next 2 products displayed on the slider by default.')
        ]);

        $fieldset->addField('display_mode', 'select', [
            'name'    => 'display_mode',
            'label'   => __('Display Mode'),
            'title'   => __('Display Mode'),
            'options' => [
                DisplayMode::TYPE_AJAX  => __('Ajax'),
                DisplayMode::TYPE_BLOCK => __('Block')
            ],
            'note'    => __('<b>Ajax display:</b> Better for performance.<br><b>Block display:</b> Better for SEO')
        ]);

        $fieldset->addField('limit_number', 'text', [
            'name'  => 'limit_number',
            'label' => __('Limit number of products'),
            'title' => __('Limit number of products')
        ]);

        $fieldset->addField('display_out_of_stock', 'select', [
            'name'    => 'display_out_of_stock',
            'label'   => __('Display "Out-of-stock" products'),
            'title'   => __('Display "Out-of-stock" products'),
            'options' => [
                '1' => __('Yes'),
                '0' => __('No')
            ]
        ]);

        $fieldset->addField('sort_order_direction', 'select', [
            'name'   => 'sort_order_direction',
            'label'  => __('Product order'),
            'title'  => __('Product order'),
            'values' => $this->direction->toOptionArray()
        ]);

        $fieldset->addField('show_see_all', 'select', [
            'name'   => 'show_see_all',
            'label'  => __('Show See All in Slider'),
            'title'  => __('Show See All in Slider'),
            'values' => $this->yesno->toOptionArray(),
            'note'   => 'If Yes, will display See All button in the slider for customers to view all products in that slider.'
        ]);

        $sliderWidth = $fieldset->addField('slider_width', 'text', [
            'name'  => 'slider_width',
            'label' => __('Slider Width (px)'),
            'title' => __('Slider Width'),
            'class' => 'validate-greater-than-zero',
            'note'  => 'Slider Width is only supported on desktop. You can leave it blank to get the appropriate slider size.'
        ]);

        $sliderHeight = $fieldset->addField('slider_height', 'text', [
            'name'  => 'slider_height',
            'label' => __('Slider Height (px)'),
            'title' => __('Slider Height'),
            'class' => 'validate-greater-than-zero',
            'note'  => 'Slider Height is only supported on desktop. You can leave it blank to get the appropriate slider size.'
        ]);

        $showNextPrev = $fieldset->addField('show_next_prev', 'select', [
            'name'   => 'show_next_prev',
            'label'  => __('Show Next/Prev Buttons'),
            'title'  => __('Show Next/Prev Buttons'),
            'values' => $this->yesno->toOptionArray(),
            'note'   => 'If Yes, Next/Prev button will be placed next to that slider.'
        ]);

        $showDotsNav = $fieldset->addField('show_dots_nav', 'select', [
            'name'   => 'show_dots_nav',
            'label'  => __('Show Dots Navigation'),
            'title'  => __('Show Dots Navigation'),
            'values' => $this->yesno->toOptionArray(),
            'note'   => 'If Yes, multiple product will be displayed in a slider, and Dots Navigation will be displayed with that slider.'
        ]);

        $sliderAutoPlay = $fieldset->addField('slider_autoplay', 'select', [
            'name'   => 'slider_autoplay',
            'label'  => __('Auto Play'),
            'title'  => __('Auto Play'),
            'values' => $this->yesno->toOptionArray(),
            'note'   => 'Select Yes to allow next product to be auto-displayed.'
        ]);

        $sliderAutoTimeout = $fieldset->addField('auto_timeout', 'text', [
            'name'  => 'auto_timeout',
            'label' => __('Auto Time-Out'),
            'title' => __('Auto Time-Out'),
            'class' => 'validate-digits validate-greater-than-zero',
        ]);

        $fieldset->addField('display_additional', 'multiselect', [
            'name'   => 'display_additional',
            'label'  => __('Display additional information'),
            'title'  => __('Display additional information'),
            'values' => $this->additional->toOptionArray(),
            'note'   => __('Select information or button(s) to display with products.')
        ]);

        $ruleType = $this->request->getParam('type');
        if (in_array($ruleType, [RuleType::TYPE_PAGE_SHOPPING, RuleType::TYPE_PAGE_PRODUCT])) {
            $fieldset->addField('add_ruc_product', 'multiselect', [
                'name'   => 'add_ruc_product',
                'label'  => __('Add Products'),
                'title'  => __('Add Products'),
                'values' => $this->addProductTypes->toOptionArray(),
                'note'   => __('Select to add Related, Up-Sell, Cross-Sell Products to the related product list')
            ]);
        }

        $fieldset->addField('product_not_displayed', 'multiselect', [
            'name'   => 'product_not_displayed',
            'label'  => __('Do not Display Product in'),
            'title'  => __('Do not Display Product in'),
            'values' => $this->productNotDisplayed->toOptionArray(),
            'note'   => __('Select to do not Displayed Product in Cart or Wishlist')
        ]);

        //set default value
        if (!$model->getId()) {
            $model->setData('show_see_all', 1);
        }

        $model->setData('slider_width', $sliderConfig['slider_width']);
        $model->setData('slider_height', $sliderConfig['slider_height']);
        $model->setData('show_next_prev', $sliderConfig['show_next_prev']);
        $model->setData('show_dots_nav', $sliderConfig['show_dots_nav']);
        $model->setData('slider_autoplay', $sliderConfig['slider_autoplay']);
        if (array_key_exists('auto_timeout', $sliderConfig)) {
            $model->setData('auto_timeout', $sliderConfig['auto_timeout']);
        }

        $form->setValues($model->getData());

        $this->setForm($form);

        $blockDependence = $this->getLayout()->createBlock(Dependence::class);
        $blockDependence->addFieldMap($layout->getHtmlId(), $layout->getName())
            ->addFieldMap($sliderWidth->getHtmlId(), $sliderWidth->getName())
            ->addFieldMap($sliderHeight->getHtmlId(), $sliderHeight->getName())
            ->addFieldMap($showNextPrev->getHtmlId(), $showNextPrev->getName())
            ->addFieldMap($showDotsNav->getHtmlId(), $showDotsNav->getName())
            ->addFieldMap($sliderAutoPlay->getHtmlId(), $sliderAutoPlay->getName())
            ->addFieldMap($sliderAutoTimeout->getHtmlId(), $sliderAutoTimeout->getName())
            ->addFieldDependence($sliderWidth->getName(), $layout->getName(), 0)
            ->addFieldDependence($sliderHeight->getName(), $layout->getName(), 0)
            ->addFieldDependence($showNextPrev->getName(), $layout->getName(), 0)
            ->addFieldDependence($showDotsNav->getName(), $layout->getName(), 0)
            ->addFieldDependence($sliderAutoPlay->getName(), $layout->getName(), 0)
            ->addFieldDependence($sliderAutoTimeout->getName(), $layout->getName(), 0)
            ->addFieldDependence($sliderAutoTimeout->getName(), $sliderAutoPlay->getName(), 1);

        $this->setChild('form_after', $blockDependence);

        return parent::_prepareForm();
    }
}
