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

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Delete
 *
 * @package Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Delete extends \Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
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
     * Delete constructor.
     *
     * @param Action\Context $context
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

    /**
     * @inheritDoc
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        /* @var \Magento\Framework\Filesystem\Directory\Write $directory */
        $directory = $this->fileSystem->getDirectoryWrite(
            DirectoryList::ROOT
        );

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('sitemap_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->sitemap;
                $model->setId($id);
                // init and load sitemap model

                /* @var $sitemap \Bss\XmlSiteMap\Model\Sitemap */
                $model->load($id);
                // delete file
                $path = $directory->getRelativePath($model->getPreparedFilename());
                if ($model->getSitemapFilename() && $directory->isFile($path)) {
                    $directory->delete($path);
                }
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the sitemap.'));
                // go to grid
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                $this->redirect('adminhtml/*/edit', ['sitemap_id' => $id]);
                return;
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to delete.'));
        // go to grid
        $this->_redirect('adminhtml/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_XmlSiteMap::bss_sitemap');
    }
}
