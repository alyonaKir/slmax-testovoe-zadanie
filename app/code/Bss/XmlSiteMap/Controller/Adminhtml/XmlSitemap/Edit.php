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
namespace Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap;

/**
 * Class Edit
 *
 * @package Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Edit extends \Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry = null;

    /**
     * @var \Bss\XmlSiteMap\Model\Sitemap
     */
    public $sitemap;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Bss\XmlSiteMap\Model\SitemapFactory
     */
    public $sitemapFactory;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\XmlSiteMap\Model\SiteMapFactory $sitemapFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\XmlSiteMap\Model\SitemapFactory $sitemapFactory
    ) {
        $this->sitemapFactory = $sitemapFactory;
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('sitemap_id');

        $model = $this->sitemapFactory->create();

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Sitemap no longer exists.'));
                /* @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('sitemap_sitemap', $model);

        // 5. Build edit form
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Google XML Sitemap') : __('New Google XML Sitemap'),
            $id ? __('Edit Google XML Sitemap') : __('New Google XML Sitemap')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Google XML Sitemap'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Google XML Sitemap'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_XmlSiteMap::bss_sitemap');
    }
}
