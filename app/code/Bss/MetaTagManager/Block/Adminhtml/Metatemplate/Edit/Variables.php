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
namespace Bss\MetaTagManager\Block\Adminhtml\Metatemplate\Edit;

/**
 * Class Variables
 *
 * @package Bss\MetaTagManager\Block\Adminhtml\Metatemplate\Edit
 */
class Variables extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Helper
     * @var \Bss\MetaTagManager\Helper\Data
     */
    protected $helper;

    /**
     * Variables constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Bss\MetaTagManager\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Bss\MetaTagManager\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Get all Store variable
     *
     * @return array
     */
    public function getStoreVariables()
    {
        return $this->helper->getStoreVariables();
    }

    /**
     * Get Category form variable
     *
     * @return array
     */
    public function getCategoryVariables()
    {
        return $this->helper->getCategoryVariables();
    }

    /**
     * Get product form variable
     *
     * @return array
     */
    public function getProductVariables()
    {
        return $this->helper->getProductVariables();
    }
}
