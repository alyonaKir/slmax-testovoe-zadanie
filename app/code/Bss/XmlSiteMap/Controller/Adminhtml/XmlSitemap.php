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
namespace Bss\XmlSiteMap\Controller\Adminhtml;

/**
 * Class XmlSitemap
 *
 * @package Bss\XmlSiteMap\Controller\Adminhtml
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
abstract class XmlSitemap extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_XmlSiteMap::bss';

    /**
     * Init actions
     *
     * @return $this
     */
    public function initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Bss_XmlSiteMap::xmlsitemap'
        )->_addBreadcrumb(
            __('Catalog'),
            __('Catalog')
        )->_addBreadcrumb(
            __('Google XML Sitemap'),
            __('Google XML Sitemap')
        );
        return $this;
    }

    /**
     * Init Page
     *
     * @param string $resultPage
     * @return string
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Bss_XmlSiteMap::xmlsitemap')
            ->addBreadcrumb(__('Bss'), __('Bss'))
            ->addBreadcrumb(__('Google XML Sitemap'), __('Google XML Sitemap'));
        return $resultPage;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_XmlSiteMap::bss_sitemap');
    }
}
