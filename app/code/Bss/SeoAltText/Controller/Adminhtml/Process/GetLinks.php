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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Controller\Adminhtml\Process;

use Bss\SeoAltText\Helper\Data as DataHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class GetLinks
 * @package Bss\SeoAltText\Controller\Adminhtml\Process
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class GetLinks extends Action
{
    /**
     * @var \Bss\SeoReport\Helper\Data
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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * GetLinks constructor.
     * @param DataHelper $dataHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param Context $context
     */
    public function __construct(
        DataHelper $dataHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        Context $context
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
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
            $page = $this->getRequest()->getPost('page');
            $productCollection = $this->getProductCollection($page);
            $linkData = [];
            foreach ($productCollection as $product) {
                $dataToAdd = [
                    'name' => $product->getName(),
                    'id' => $product->getId()
                ];
                $linkData[] = $dataToAdd;
            }
            if (!empty($linkData)) {
                $dataReturn['status'] = true;
                $dataReturn['data'] = $linkData;
            } else {
                $dataReturn['error_type'] = 'empty';
            }
        } else {
            $dataReturn['error_type'] = 'bad_request';
        }

        $result->setData($dataReturn);
        return $result;
    }

    /**
     * @param int $page
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($page = 1)
    {
        $pageSize = DataHelper::SEO_ALT_TEXT_PER_PAGE;
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('id');
        $collection->addAttributeToSelect('name');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_SeoAltText::seo_alt_text');
    }
}
