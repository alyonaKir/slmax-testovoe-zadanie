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
 * Class Generate
 *
 * @package Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Generate extends \Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    public $fileSystem;

    /**
     * @var \Bss\XmlSiteMap\Model\Sitemap
     */
    public $sitemap;

    /**
     * Generate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\XmlSiteMap\Model\Sitemap $sitemap
     * @param \Magento\Framework\Filesystem $fileSystem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\XmlSiteMap\Model\Sitemap $sitemap,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        $this->fileSystem = $fileSystem;
        $this->sitemap = $sitemap;
        parent::__construct($context);
    }

    public function execute()
    {
        // init and load sitemap model
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = $this->sitemap;
        /* @var $sitemap \Bss\XmlSiteMap\Model\Sitemap */
        $sitemap->load($id);
        // if sitemap record exists
        if ($sitemap->getId()) {
            try {
                $sitemap->generateXml();

                $this->messageManager->addSuccessMessage(
                    __('The sitemap "%1" has been generated.', $sitemap->getData('xml_sitemap_filename'))
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t generate the sitemap right now. ' . $e->getMessage()));
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to generate.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        // go to grid
        return $resultRedirect->setPath('adminhtml/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_XmlSiteMap::bss_sitemap');
    }
}
