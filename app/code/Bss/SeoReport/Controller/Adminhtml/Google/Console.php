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
namespace Bss\SeoReport\Controller\Adminhtml\Google;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Console
 * @package Bss\SeoReport\Controller\Adminhtml\Google
 */
class Console extends Action
{
    /**
     * @var \Bss\SeoReport\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Bss\SeoReport\Helper\GoogleAPI
     */
    protected $googleAPI;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Console constructor.
     * @param \Bss\SeoReport\Helper\Data $dataHelper
     * @param \Bss\SeoReport\Helper\GoogleAPI $googleAPI
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        \Bss\SeoReport\Helper\Data $dataHelper,
        \Bss\SeoReport\Helper\GoogleAPI $googleAPI,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->googleAPI = $googleAPI;
        $this->dataHelper = $dataHelper;
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
            $valueRequest = $this->getRequest()->getPost('value');
            $typeRequest = $this->getRequest()->getPost('type');

            $refreshCode = $this->dataHelper->getRefreshToken();
            $accessObject = $this->googleAPI->refreshTokenUser($refreshCode);

            $siteInfoObject = [];
            if (isset($accessObject['access_token']) && isset($accessObject['token_type'])) {
                $accessToken = $accessObject['access_token'];
                $tokenType = $accessObject['token_type'];

                $siteUrl = $this->getBaseUrl();

                $startDate = $this->dataHelper->getStartDate();
                $endDate = $this->dataHelper->getEndDate();

                $dimensionsObject = [$typeRequest];
                $filterObject = [];

                $filterObject[] = [
                    "dimension" => $typeRequest,
                    "operator" => "contains",
                    "expression" => $valueRequest
                ];
                $dataJson = $this->googleAPI->getGoogleConsoleByKeyword(
                    $startDate,
                    $endDate,
                    $dimensionsObject,
                    $filterObject
                );
                $dataReturn['query'] = $dataJson;
                $siteInfoObject = $this->googleAPI->getSiteInfo($siteUrl, $accessToken, $tokenType, $dataJson);
                if ($siteInfoObject) {
                    $dataReturn["status"] = true;
                    $dataReturn["data"] = $siteInfoObject;
                } else {
                    $dataReturn['error_type'] = 'not_connect';
                }
            } else {
                $dataReturn['error_type'] = 'permission';
            }
        } else {
            $dataReturn['error_type'] = 'bad_request';
        }

        $result->setData($dataReturn);
        return $result;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore($this->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
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
