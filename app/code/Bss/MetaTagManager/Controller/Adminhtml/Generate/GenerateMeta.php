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
namespace Bss\MetaTagManager\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class GenerateMeta
 * @package Bss\MetaTagManager\Controller\Adminhtml\Generate
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateMeta extends Action
{
    /**
     * @var \Bss\MetaTagManager\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\MetaTagManager\Model\MetaTemplateFactory
     */
    private $metaTemplateFactory;

    /**
     * @var \Bss\MetaTagManager\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Bss\MetaTagManager\Helper\ProcessMetaTemplate
     */
    private $processMetaTemplate;

    private $registry;

    /**
     * GenerateMeta constructor.
     * @param \Bss\MetaTagManager\Helper\Data $dataHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
     * @param \Bss\MetaTagManager\Model\RuleFactory $ruleFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
     * @param Context $context
     */
    public function __construct(
        \Bss\MetaTagManager\Helper\Data $dataHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory,
        \Bss\MetaTagManager\Model\RuleFactory $ruleFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate,
        \Magento\Framework\Registry $registry,
        Context $context
    ) {
        $this->processMetaTemplate = $processMetaTemplate;
        $this->productFactory = $productFactory;
        $this->ruleFactory = $ruleFactory;
        $this->metaTemplateFactory = $metaTemplateFactory;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $dataReturn = [
            "status" => false,
            "data" => [],
            "error_type" => ""
        ];
        if ($this->getRequest()->isAjax()) {
            $productId = $this->getRequest()->getPost('product_id');
            $metaId = $this->getRequest()->getPost('meta_id');
        } else {
            $productId = $this->getRequest()->getParam('product_id');
            $metaId = $this->getRequest()->getParam('meta_id');
        }
        if ($productId && $metaId) {
            $statusGenerate = $this->generateProductFromId($productId, $metaId);
            if ($statusGenerate) {
                $dataReturn['status'] = true;
            } else {
                $dataReturn['error_type'] = 'exist';
            }
        } else {
            $dataReturn['error_type'] = 'invalid_param';
        }
        $result->setData($dataReturn);
        return $result;
    }

    /**
     * @param string $productId
     * @param string $metaId
     * @return $this|bool
     * @throws \Exception
     */
    protected function generateProductFromId($productId, $metaId)
    {
        $inputMetaData = [];
        $productObject = $this->productFactory->create()->setStoreId(0)->load($productId);
        //Check Product Id in Conditions
        $collection = $this->metaTemplateFactory->create()
            ->getCollection()
            ->addFieldToFilter('meta_type', 'product')
            ->addFieldToFilter('status', '1');

        if ($collection->getSize()) {
            $productCheckObject = [];
            //Check Collection Priority
            foreach ($collection as $metaObject) {
                $metaData = $metaObject->getData();
                $priority = $metaData['priority'];
                $modelRule = $this->ruleFactory->create();
                $modelRule->setMetaData($metaData);
                $statusValidate = $modelRule->validateProductConditions($productObject);
                if ($statusValidate) {
                    $dataToAdd = [
                        'id' => $metaData['id'],
                        'priority' => $priority,
                        'store' => $metaData['store']
                    ];
                    $productCheckObject[] = $dataToAdd;
                }
                //HandleData
                if ((int)$metaId === (int)$metaData['id']) {
                    $inputMetaData = $metaObject;
                }
            }
            //Check in ProductCheckObject
            if (!empty($productCheckObject)) {
                return $this->processGenerateProduct(
                    $productCheckObject,
                    $inputMetaData,
                    $productObject,
                    $metaId
                );
            }
        }
        return false;
    }

    /**
     * @param object $productCheckObject
     * @param object $inputMetaData
     * @param object $productObject
     * @param string $metaId
     * @return $this|bool
     * @throws \Exception
     */
    public function processGenerateProduct(
        $productCheckObject,
        $inputMetaData,
        $productObject,
        $metaId
    ) {
        $finalId = $this->getFinalId($productCheckObject);
        $inputMetaDataStore = $inputMetaData->getData('store');
        $inputMetaDataStoreArray = explode(',', $inputMetaDataStore);
        $inputMetaDataId = $inputMetaData->getData('id');
        $currentStorePriority = $inputMetaData->getData('priority');
        foreach ($inputMetaDataStoreArray as $key => $storeId) {
            foreach ($productCheckObject as $itemProduct) {
                if ((int)$itemProduct['id'] !== (int)$inputMetaDataId) {
                    $dataToCheckStoreArray = explode(',', $itemProduct['store']);
                    $priorityToCheckStore = $itemProduct['priority'];
                    if ((int)$priorityToCheckStore >= (int)$currentStorePriority && in_array($storeId, $dataToCheckStoreArray)) {
                        unset($inputMetaDataStoreArray[$key]);
                    }
                }
            }
        }
        //Check if this finalID correct with Meta ID
        if ((int)$finalId === (int)$metaId) {
            $inputMetaDataStoreArray[] = "0";
        }

        if (!empty($inputMetaDataStoreArray)) {
            return $this->generateProduct($productObject, $inputMetaData, $inputMetaDataStoreArray);
        }
        return false;
    }

    /**
     * @param object $productCheckObject
     * @return int
     */
    public function getFinalId($productCheckObject)
    {
        $maxPriority = 0;
        $finalId = 0;
        foreach ($productCheckObject as $item) {
            $priorityItem = $item['priority'];
            if ((int)$priorityItem >= $maxPriority) {
                $maxPriority = (int)$priorityItem;
                $finalId = (int)$item['id'];
            }
        }
        return $finalId;
    }

    /**
     * @param object $product
     * @param object $metaTemplate
     * @param array $allowedStore
     * @return bool
     * @throws \Exception
     */
    public function generateProduct($product, $metaTemplate, $allowedStore = [])
    {
        $statusGenerate = false;
        $productId = $product->getId();
        $productWebsiteIds = $product->getWebsiteIds();
        $stores = $this->storeManager->getStores(false);
        $productExcludeTemplate = $product->getData('excluded_meta_template');

        if ($productExcludeTemplate !== '1' && !empty($allowedStore) && in_array("0", $allowedStore)) {
            $this->processMetaTemplate->processProductMeta($product, $metaTemplate);
            $statusGenerate = true;
        }

        foreach ($stores as $store) {
            $storeId = $store->getId();
            if (!empty($allowedStore) && !in_array((string)$store->getId(), $allowedStore)) {
                continue;
            }
            $websiteId = $store->getWebsiteId();
            if (in_array($websiteId, $productWebsiteIds)) {
                $this->registry->unregister('bss_store_id');
                $this->registry->register('bss_store_id', $storeId);
                //Create New Product
                $productObject = $this->productFactory->create()->setStoreId($storeId)->load($productId);
                $productExcludeTemplateStore = $productObject->getData('excluded_meta_template');
                if ($productExcludeTemplateStore !== '1') {
                    $this->processMetaTemplate->processProductMeta($productObject, $metaTemplate);
                    $statusGenerate = true;
                }
            }
        }
        return $statusGenerate;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_MetaTagManager::meta_template');
    }
}
