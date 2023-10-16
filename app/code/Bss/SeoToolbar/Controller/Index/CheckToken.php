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
 * @package    Bss_SeoToolbar
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoToolbar\Controller\Index;

use Magento\Framework\App\Action\Context;

/**
 * Class CheckToken
 * @package Bss\SeoToolbar\Controller\Index
 */
class CheckToken extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Bss\SeoToolbar\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    public $countryFactory;

    /**
     * @var Context
     */
    public $context;

    /**
     * @var \Magento\Framework\Url\Encoder
     */
    public $urlEncoder;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * CheckToken constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Bss\SeoToolbar\Helper\Data $moduleHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Url\Encoder $urlEncoder
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Session\Generic $session
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Bss\SeoToolbar\Helper\Data $moduleHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Url\Encoder $urlEncoder,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Session\Generic $session
    ) {
        $this->backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
        $this->moduleHelper = $moduleHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->context = $context;
        $this->countryFactory = $countryFactory;
        $this->urlEncoder = $urlEncoder;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $status = false;
        $sysMessage = '';
        $dataReturn = [];
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $tokenValue = $this->getRequest()->getPost('token');

            //Check Token
            $dataDecode = $this->moduleHelper->decodeData($tokenValue);
            $statusModule = $this->moduleHelper->isEnableModule();
            if ($dataDecode && (int)$statusModule) {
                $currentTime = time();
                if (isset($dataDecode['expired']) && (int)$dataDecode['expired'] >= $currentTime) {
                    $status = true;
                    $entityId = $this->getRequest()->getPost('entity_id');
                    $entityType = $this->getRequest()->getPost('entity_type');
                    $urlBackend = $this->getUrlBackend($entityId, $entityType);
                    $dataReturn = [
                        "expired" => (int)$dataDecode['expired'],
                        "backend_url" => $urlBackend
                    ];
                    $sysMessage = 'Get token Info Successfully';
                }
            } else {
                $sysMessage = 'Token is Not correct';
            }
        } else {
            $sysMessage = 'Bad Request';
        }

        $dataResult = [
            'status' => $status,
            'message' => $sysMessage,
            'data' => $dataReturn
        ];
        $result->setData($dataResult);
        return $result;
    }

    /**
     * @param string $entityId
     * @param string $entityType
     * @return string
     */
    protected function getUrlBackend($entityId, $entityType)
    {
        if ($entityType === 'product') {
            $backendUrl = $this->backendUrl->getUrl('catalog/' . $entityType . '/edit/id/' . $entityId);
        } elseif ($entityType === 'category') {
            $backendUrl = $this->backendUrl->getUrl('catalog/' . $entityType . '/edit/id/' . $entityId);
        } elseif ($entityType === 'cms-page') {
            $backendUrl = $this->backendUrl->getUrl('cms/page/edit/page_id/' . $entityId);
        } else {
            $backendUrl = '';
        }
        return $backendUrl;
    }

    /**
     * @param string $countryCode
     * @return string
     */
    protected function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    /**
     * @param string $countryCode
     * @return bool|\Magento\Store\Api\Data\StoreInterface
     */
    protected function getStoreByCountryCode($countryCode)
    {
        //Get All Store view
        $stores = $this->storeManager->getStores(false);

        foreach ($stores as $store) {
            $countryStore = $this->moduleHelper->getCountries($store->getId());

            if (strpos($countryStore, $countryCode) !== false) {
                return $store;
            }
        }
        return false;
    }
}
