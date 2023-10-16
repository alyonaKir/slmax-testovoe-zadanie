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
 * @package    Bss_SeoToolbar
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoToolbar\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Check
 * @package Bss\SeoToolbar\Block\System\Config
 */
class Check extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Bss_SeoToolbar::system/config/seo_toolbar.phtml';

    /**
     * @var \Bss\SeoToolbar\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var Context
     */
    protected $context;

    const THIRTY_DAY_TO_SECOND = 2592000;

    /**
     * Check constructor.
     * @param Context $context
     * @param \Bss\SeoToolbar\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Bss\SeoToolbar\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->context = $context;
        $this->dataHelper = $dataHelper;
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
     * @return string
     */
    public function getTokenCode()
    {
        $currentTimeNumber = time();
        $expiredNumber = $currentTimeNumber + self::THIRTY_DAY_TO_SECOND;
        $dataInput = [
            "expired" => $expiredNumber
        ];
        $dataCode = $this->dataHelper->encodeData($dataInput);
        return $dataCode;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'google_authorization',
                'label' => __('Start Check')
            ]
        );
        return $button->toHtml();
    }
}