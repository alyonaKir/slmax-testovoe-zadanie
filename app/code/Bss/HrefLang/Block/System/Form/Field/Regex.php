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
 * @package    Bss_HrefLang
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HrefLang\Block\System\Form\Field;

/**
 * Class Regex
 *
 * @package Bss\HrefLang\Block\System\Form\Field
 */
class Regex extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = [];

    /**
     * @var $customerGroupRenderer
     */
    protected $customerGroupRenderer;

    /**
     * @var $countryRender
     */
    protected $countryRender;

    /**
     * @var $languageRender
     */
    protected $languageRender;

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
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Return country group element
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCustomerGroupRenderer()
    {
        if (!$this->customerGroupRenderer) {
            $this->customerGroupRenderer = $this->getLayout()->createBlock(
                '\Bss\HrefLang\Block\System\Form\Field\CustomerGroup',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->customerGroupRenderer;
    }

    /**
     * Get country render html
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCountryRender()
    {
        if (!$this->countryRender) {
            $this->countryRender = $this->getLayout()->createBlock(
                '\Bss\HrefLang\Block\System\Form\Field\CountryCode',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->countryRender;
    }

    /**
     * Get language render html
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getLanguageRender()
    {
        if (!$this->languageRender) {
            $this->languageRender = $this->getLayout()->createBlock(
                '\Bss\HrefLang\Block\System\Form\Field\Country',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->languageRender;
    }

    /**
     * @inheritDoc
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'store',
            [
                'label' => __('Store View'),
                'renderer' => $this->getCustomerGroupRenderer(),
            ]
        );
        $this->addColumn(
            'language',
            [
                'label' => __('Language'),
                'renderer' => $this->getLanguageRender(),
            ]
        );
        $this->addColumn(
            'country',
            [
                'label' => __('Country'),
                'renderer' => $this->getCountryRender(),
            ]
        );
        // $this->addColumn('active', array('label' => __('Active')));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @inheritDoc
     *
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $customerGroup = $row->getData('store');
        $country = $row->getData('country');
        $language = $row->getData('language');
        $options = [];
        if ($customerGroup) {
            $options[
                'option_' . $this->getCustomerGroupRenderer()->calcOptionHash($customerGroup)
            ] = 'selected="selected"';
            $options['option_' . $this->getCountryRender()->calcOptionHash($country)] = 'selected="selected"';
            $options['option_' . $this->getLanguageRender()->calcOptionHash($language)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
