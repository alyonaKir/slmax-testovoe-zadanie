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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Insert
 * @package Bss\SeoAltText\Block\System\Config
 */
class Insert extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Bss_SeoAltText::system/config/insert.phtml';

    /**
     * @var Context
     */
    protected $context;
    /**
     * @var \Bss\SeoAltText\Helper\Data
     */
    private $helperData;

    /**
     * Insert constructor.
     * @param Context $context
     * @param \Bss\SeoAltText\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Bss\SeoAltText\Helper\Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->context->getStoreManager()->getStore()->getBaseUrl();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInsertButton()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'bss_insert_button',
                'label' => __('Insert Variables')
            ]
        );
        return $button->toHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInsertButtonSecond()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'bss_insert_button_second',
                'label' => __('Insert Variables')
            ]
        );
        return $button->toHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGenerateButton()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'bss_generate_product',
                'label' => __('Start Generating')
            ]
        );
        return $button->toHtml();
    }

    /**
     * Get all Store variable
     *
     * @return array
     */
    public function getStoreVariables()
    {
        return $this->helperData->getStoreVariables();
    }

    /**
     * Get Category form variable
     *
     * @return array
     */
    public function getCategoryVariables()
    {
        return $this->helperData->getCategoryVariables();
    }

    /**
     * Get product form variable
     *
     * @return array
     */
    public function getProductVariables()
    {
        return $this->helperData->getProductVariables();
    }

    /**
     * @return string
     */
    public function getGenerateLink()
    {
        return $this->getUrl('bss_alt_text/album/generatealbum');
    }
}