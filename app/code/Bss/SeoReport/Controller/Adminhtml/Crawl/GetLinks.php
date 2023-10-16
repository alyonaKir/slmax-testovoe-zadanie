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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Controller\Adminhtml\Crawl;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class GetLinks
 * @package Bss\SeoReport\Controller\Adminhtml\Crawl
 */
class GetLinks extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Bss\SeoReport\Model\ReportLinksFactory
     */
    protected $reportLinksFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * GetLinks constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\SeoReport\Model\ReportLinksFactory $reportLinksFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\SeoReport\Model\ReportLinksFactory $reportLinksFactory,
        Context $context
    ) {
        $this->reportLinksFactory = $reportLinksFactory;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
            $dataUrl = $this->getProductLink($page);
            $linkData = [];
            foreach ($dataUrl as $value) {
                $requestPath = ltrim($value['request_path'], "/");
                $linkData[] = $this->handleUrl($requestPath, $value['store_id']);
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
     * @param string $requestPath
     * @param string $storeId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleUrl($requestPath, $storeId)
    {
        $dataReturn = [
            "main_url" => "",
            "path" => $requestPath
        ];
        $dataReturn["main_url"] = $this->getBaseUrl($storeId);
        return $dataReturn;
    }

    /**
     * @param int $page
     * @return mixed
     */
    public function getProductLink($page = 1)
    {
        $pageSize = \Bss\SeoReport\Helper\Data::SEO_REPORT_CRAWL_MAX_URL;
        $reportLinksFactory = $this->reportLinksFactory->create()->getCollection();
        $reportLinksFactory->setPageSize($pageSize);
        $reportLinksFactory->setCurPage($page);
        return $reportLinksFactory->getData();
    }

    /**
     * @param string $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl($storeId)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
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
        return $this->_authorization->isAllowed('Magento_Backend::admin');
    }
}
