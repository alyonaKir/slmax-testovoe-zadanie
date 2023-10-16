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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller;

/**
 * Class Save
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @package Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Bss\XmlSiteMap\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\AvailablePath
     */
    protected $availablePath;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Bss\XmlSiteMap\Model\Sitemap
     */
    protected $sitemap;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Bss\XmlSiteMap\Model\Sitemap $sitemap
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\MediaStorage\Model\File\Validator\AvailablePath $availablePath
     * @param \Bss\XmlSiteMap\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Bss\XmlSiteMap\Model\Sitemap $sitemap,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\Validator\AvailablePath $availablePath,
        \Bss\XmlSiteMap\Helper\Data $dataHelper
    ) {
        $this->backendSession = $context->getSession();
        $this->dataHelper = $dataHelper;
        $this->availablePath = $availablePath;
        $this->fileSystem = $fileSystem;
        $this->sitemap = $sitemap;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Validate path
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    protected function validatePath(array $data)
    {
        if (!empty($data['xml_sitemap_filename']) && !empty($data['xml_sitemap_path'])) {
            $data['xml_sitemap_path'] = '/' . ltrim($data['xml_sitemap_path'], '/');
            $path = rtrim($data['xml_sitemap_path'], '\\/') . '/' . $data['xml_sitemap_filename'];
            /** @var $validator \Magento\MediaStorage\Model\File\Validator\AvailablePath */
            $validator = $this->availablePath;
            /** @var $helper \Bss\XmlSiteMap\Helper\Data */
            $helper = $this->dataHelper;
            $validator->setPaths($helper->getValidPaths());
            if (!$validator->isValid($path)) {
                foreach ($validator->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                // save data in session
                $this->backendSession->setFormData($data);
                // redirect to edit form
                return false;
            }
        }
        return true;
    }

    /**
     * Clean up site map
     *
     * @param \Bss\XmlSiteMap\Model\Sitemap $model
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function clearSiteMap(\Bss\XmlSiteMap\Model\Sitemap $model)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $directory */
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::ROOT);

        if ($this->getRequest()->getParam('sitemap_id')) {
            $model->load($this->getRequest()->getParam('sitemap_id'));
            $fileName = $model->getSitemapFilename();

            $path = $model->getSitemapPath() . '/' . $fileName;
            if ($fileName && $directory->isFile($path)) {
                $directory->delete($path);
            }
        }
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function saveData($data)
    {
        // init model and set data
        /** @var \Bss\XmlSiteMap\Model\Sitemap $model */
        $model = $this->sitemap;
        $this->clearSiteMap($model);
        $model->setData($data);

        // try to save it
        try {
            // save the data
            $model->save();
            // display success message
            $this->messageManager->addSuccess(__('You saved the sitemap.'));
            // clear previously saved data from session
            $this->backendSession->setFormData(false);
            return $model->getId();
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addError($e->getMessage());
            // save data in session
            $this->backendSession->setFormData($data);
        }
        return false;
    }

    /**
     * Get result after saving data
     *
     * @param string|bool $id
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function getResult($id)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);
        if ($id) {
            // check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('adminhtml/*/edit', ['sitemap_id' => $id]);
                return $resultRedirect;
            }
            // go to grid or forward to generate action
            if ($this->getRequest()->getParam('generate')) {
                $this->getRequest()->setParam('sitemap_id', $id);
                return $this->resultFactory->create(Controller\ResultFactory::TYPE_FORWARD)
                    ->forward('generate');
            }
            $resultRedirect->setPath('adminhtml/*/');
            return $resultRedirect;
        }
        $resultRedirect->setPath(
            'adminhtml/*/edit',
            ['sitemap_id' => $this->getRequest()->getParam('sitemap_id')]
        );
        return $resultRedirect;
    }

    /**
     * @inheritDoc
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function execute()
    {
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        /* @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);
        if ($data) {
            if (!$this->validatePath($data)) {
                $resultRedirect->setPath(
                    'adminhtml/*/edit',
                    ['sitemap_id' => $this->getRequest()->getParam('sitemap_id')]
                );
                return $resultRedirect;
            }
            return $this->getResult($this->saveData($data));
        }
        $resultRedirect->setPath('adminhtml/*/');
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_XmlSiteMap::bss_sitemap');
    }
}
