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
namespace Bss\XmlSiteMap\Model\Config\Backend;

/**
 * Class DisableProduct
 *
 * @package Bss\XmlSiteMap\Model\Config\Backend
 */
class DisableProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    public $pageFactory;

    /**
     * @var \Magento\Cms\Model\Page
     */
    public $page;

    /**
     * DisableProduct constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\PageFactory $pageFactory,
        array $data = []
    ) {
        $this->pageFactory = $pageFactory;
        $this->page = $page;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'simple', 'label' => __('Simple Product')],
            ['value' => 'configurable', 'label' => __('Configurable Product')],
            ['value' => 'grouped', 'label' => __('Grouped Product')],
            ['value' => 'virtual', 'label' => __('Virtual Product')],
            ['value' => 'bundle', 'label' => __('Bundle Product')],
            ['value' => 'downloadable', 'label' => __('Downloadable Product')],
        ];
    }
}
