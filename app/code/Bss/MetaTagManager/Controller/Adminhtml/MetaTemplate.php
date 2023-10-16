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
namespace Bss\MetaTagManager\Controller\Adminhtml;

/**
 * Class MetaTemplate
 * @package Bss\MetaTagManager\Controller\Adminhtml
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
abstract class MetaTemplate extends \Magento\Backend\App\Action
{
    /**
     * Core Registry
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    const ADMIN_RESOURCE = 'Bss_MetaTagManager::top_level';

    /**
     * MetaTemplate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init Page
     * @param string $resultPage
     * @return string
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Experius_Test::top_level')
            ->addBreadcrumb(__('Bss'), __('Bss'))
            ->addBreadcrumb(__('Meta Template'), __('Meta Template'));
        return $resultPage;
    }

    /**
     * Is Allowed
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_MetaTagManager::meta_template');
    }
}
