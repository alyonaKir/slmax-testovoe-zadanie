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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Block\Style;

/**
 * Class Style
 * @package Bss\XmlSiteMap\Block\Style
 */
class Style extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\XmlSiteMap\Helper\Data
     */
    private $dataHelper;

    /**
     * Style constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Bss\XmlSiteMap\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Bss\XmlSiteMap\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Bss\XmlSiteMap\Helper\Data
     */
    public function getHelper()
    {
        return  $this->dataHelper;
    }
}
