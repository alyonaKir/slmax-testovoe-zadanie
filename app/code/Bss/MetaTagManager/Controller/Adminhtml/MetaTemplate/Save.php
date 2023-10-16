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

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 *
 * @package Bss\MetaTagManager\Controller\Adminhtml\MetaTemplate
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\MetaTagManager\Model\MetaTemplateFactory
     */
    protected $metaTemplateFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $modelDate;

    /**
     * @var \Bss\MetaTagManager\Helper\ProcessMetaTemplate
     */
    private $processMetaTemplate;
    /**
     * @var \Bss\MetaTagManager\Model\RuleFactory
     */
    private $ruleFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\MetaTagManager\Model\RuleFactory $ruleFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
     * @param \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\MetaTagManager\Model\RuleFactory $ruleFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->processMetaTemplate = $processMetaTemplate;
        $this->modelDate = $modelDate;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('id');
            $model = $this->ruleFactory->create()->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Meta Template no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $data = $this->processData($data, $id);
            $data = $this->processInputData($data);
            $model->loadPost($data);
            $model->setData($data);
            try {
                $model->save();
                //Check if is Category Generate
                if (isset($data['meta_type']) && $data['meta_type'] === 'category') {
                    if (isset($data['category'])) {
                        $categoyId = $data['category'];
                        $this->generateCategoryTemplate($categoyId, $model);
                        $this->messageManager->addSuccessMessage(__('Save and Generate Meta Template successfully!'));
                        return $resultRedirect->setPath('*/*/', ['id' => $model->getId()]);
                    } else {
                        $this->messageManager->addErrorMessage(__('Category is not valid. Please try again!'));
                    }
                } else {
                    $this->messageManager->addSuccessMessage(__('Save successfully. Please generate Meta Template!'));
                    return $resultRedirect->setPath('*/*/generate', ['id' => $model->getId()]);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->logger->critical($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager
                    ->addExceptionMessage($e, 'Something went wrong while saving Meta Template. ' . $e->getMessage());
                $this->logger->critical($e->getMessage());
            }

            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $data
     * @param string $id
     * @return mixed
     */
    public function processData($data, $id)
    {
        $currentTime = $this->modelDate->gmtDate('Y-m-d H:i:s');
        if (!$id) {
            $data['created_at'] = $currentTime;
        }
        $data['updated_at'] = $currentTime;
        $store = implode($data['store'], ',');
        $data['store'] = $store;
        if (isset($data['category'])) {
            $categories = implode($data['category'], ',');
            $data['category'] = $categories;
        }
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processInputData($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        if (isset($data['rule']) && isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);
        }
        return $data;
    }

    /**
     * @param string $categoryIds
     * @param object $template
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws LocalizedException
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
