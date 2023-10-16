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
namespace Bss\MetaTagManager\Controller\Adminhtml\MetaTemplate;

/**
 * Class Generate
 * @package Bss\MetaTagManager\Controller\Adminhtml\MetaTemplate
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Generate extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Bss\MetaTagManager\Model\MetaTemplateFactory
     */
    private $metaTemplateFactory;

    /**
     * @var \Bss\MetaTagManager\Helper\ProcessMetaTemplate
     */
    private $processMetaTemplate;

    /**
     * Generate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
     * @param \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
     */
    public function __construct(
       \Magento\Backend\App\Action\Context $context,
       \Magento\Framework\Registry $coreRegistry,
       \Magento\Framework\View\Result\PageFactory $resultPageFactory,
       \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory,
       \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
    ) {
        $this->processMetaTemplate = $processMetaTemplate;
        $this->metaTemplateFactory = $metaTemplateFactory;
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->metaTemplateFactory->create();

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Meta Template no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            } else {
                if ($model->getMetaType() === 'product') {
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->prepend(__('Generate Meta Template'));
                    return $resultPage;
                } else {
                    $categoryId = $model->getCategory();
                    $this->generateCategoryTemplate($categoryId, $model);
                    $this->messageManager->addSuccessMessage(__('Generate Meta Template successfully!'));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/', ['id' => $model->getId()]);
                }
            }
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * @param string $categoryIds
     * @param object $template
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function generateCategoryTemplate($categoryIds, $template)
    {
        $categoryIdArray = explode(',', $categoryIds);
        if (!empty($categoryIdArray)) {
            foreach ($categoryIdArray as $categoryId) {
                $this->processMetaTemplate->processCategoryMeta($categoryId, $template);
            }
        }
        return $this;
    }
    /**
     * Is Allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_MetaTagManager::meta_template');
    }
}
