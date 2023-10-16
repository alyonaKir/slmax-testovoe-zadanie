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
 * @package    Bss_RobotsMetaTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RobotsMetaTag\Block\System\Form\Field;

/**
 * Class Robots
 *
 * @package Bss\RobotsMetaTag\Block\System\Form\Field
 */
class Robots extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = [];

    /**
     * @var $optionRenderer
     */
    protected $optionRenderer;

    /**
     * Enable the "Add after" button or not
     *
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * @var $_addButtonLabel
     */
    protected $_addButtonLabel;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Get option render
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getOptionRenderer()
    {
        if (!$this->optionRenderer) {
            $this->optionRenderer = $this->getLayout()->createBlock(
                '\Bss\RobotsMetaTag\Block\System\Form\Field\Option',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->optionRenderer;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('url', ['label' => __('URL Pattern')]);
        $this->addColumn(
            'option',
            [
                'label' => __('Option'),
                'renderer' => $this->getOptionRenderer(),
            ]
        );
        // $this->addColumn('active', array('label' => __('Active')));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $myOption = $row->getData('option');
        $options = [];
        if ($myOption) {
            $options['option_' . $this->getOptionRenderer()->calcOptionHash($myOption)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == "url") {
            $this->_columns[$columnName]['class'] = 'input-text required-entry validate-text';
            $this->_columns[$columnName]['style'] = 'width:250px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
