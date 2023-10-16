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

use DOMDocument;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Crawl
 * @package Bss\SeoReport\Controller\Adminhtml\Crawl
 */
class Crawl extends Action
{
    const ONE_DAY_TO_SECONDS = 86400;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var DOMDocument
     */
    protected $DOMDocument;
    /**
     * @var \Bss\SeoReport\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;
    /**
     * @var \Bss\SeoReport\Helper\SeoReportHelper
     */
    protected $seoReportHelper;
    /**
     * @var Json
     */
    private $jsonHelper;

    /**
     * Crawl constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\SeoReport\Model\UrlRewriteFactory $urlRewriteFactory
     * @param \Bss\SeoReport\Helper\SeoReportHelper $seoReportHelper
     * @param Json $jsonHelper
     * @param DOMDocument $DOMDocument
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\SeoReport\Model\UrlRewriteFactory $urlRewriteFactory,
        \Bss\SeoReport\Helper\SeoReportHelper $seoReportHelper,
        Json $jsonHelper,
        DOMDocument $DOMDocument,
        Context $context
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->DOMDocument = $DOMDocument;
        $this->storeManager = $storeManager;
        $this->seoReportHelper = $seoReportHelper;
        $this->resultJsonFactory = $resultJsonFactory;
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
            $mainUrl = $this->getRequest()->getPost('main_url');
            $path = $this->getRequest()->getPost('path');
        } else {
            $mainUrl = $this->getRequest()->getParam('main_url');
            $path = $this->getRequest()->getParam('path');
        }
        if ($mainUrl && $path) {
            $dataReturn['status'] = true;
            $url = $mainUrl . $path;
            $dataReturn['data']['url'] = $url;
            $pageData = (string)$this->phpCrawPage($url);
            if ($pageData && $pageData !== '' && $pageData !== null) {
                $dataReturn['data']['content'] = $this->handlePage($pageData, $mainUrl, $path);
            } else {
                $dataReturn['error_type'] = 'not_found';
            }
        } else {
            $dataReturn['error_type'] = 'invalid_param';
        }
        $result->setData($dataReturn);
        return $result;
    }

    /**
     * @param string $mainUrl
     * @return int|string
     */
    public function getStoreIdFromMainUrl($mainUrl)
    {
        $stores = $this->storeManager->getStores(false);
        $storeId = '0';
        foreach ($stores as $store) {
            $baseUrl = $store->getBaseUrl();
            $baseUrl = trim($baseUrl, '/');
            $mainUrl = trim($mainUrl, '/');
            if ($baseUrl === $mainUrl) {
                $storeId = $store->getId();
            }
        }
        return $storeId;
    }

    /**
     * @param string $content
     * @param string $mainUrl
     * @param string $path
     * @return array|bool
     */
    public function handlePage($content, $mainUrl, $path)
    {
        $storeId = $this->getStoreIdFromMainUrl($mainUrl);
        $collection = $this->urlRewriteFactory->create()
            ->getCollection()
            ->addFieldToFilter('request_path', $path)
            ->addFieldToFilter('store_id', $storeId);
        $urlRewriteData = $collection->getData();
        if (isset($urlRewriteData[0])) {
            $urlRewriteId = $urlRewriteData[0]['url_rewrite_id'];
            //Get Url Rewrite Collection
            $dom = $this->DOMDocument;
            @$dom->loadHTML($content);
            $dataReturn = [
                "canonical" => 0,
                "headings" => '',
                "images" => 0,
                "open_graph" => 0,
                "twitter_card" => 0
            ];
            $dataReturn['canonical'] = $this->handleCanonicalTagDOM($dom);
            $dataReturn['headings'] = $this->handleHeadingsDOM($dom);
            $dataReturn['images'] = $this->handleImagesDOM($dom);
            $dataReturn['open_graph'] = ($this->handleOpenGraphDOM($dom)) ? 1 : 0;
            $dataReturn['twitter_card'] = ($this->handleTwitterCardDOM($dom)) ? 1 : 0;
            //Update to Database
            $expiredTime = time() + self::ONE_DAY_TO_SECONDS;
            $dataInsert = [
                'url_rewrite_id' => $urlRewriteId,
                'canonical_tag' => $dataReturn['canonical'],
                'headings' => $dataReturn['headings'],
                'images' => $dataReturn['images'],
                'open_graph' => $dataReturn['open_graph'],
                'twitter_card' => $dataReturn['twitter_card'],
                'expired' => $expiredTime
            ];
            $this->seoReportHelper->handleData($dataInsert);
            return $dataReturn;
        } else {
            return false;
        }
    }

    /**
     * @param object $dom
     * @return false|string
     */
    public function handleHeadingsDOM($dom)
    {
        $h1ElementNumber = $this->getElementTagNumber($dom, "h1");
        $h2ElementNumber = $this->getElementTagNumber($dom, "h2");
        $h3ElementNumber = $this->getElementTagNumber($dom, "h3");
        $h4ElementNumber = $this->getElementTagNumber($dom, "h4");
        $h5ElementNumber = $this->getElementTagNumber($dom, "h5");

        $headingsObject = [
            "h1" => $h1ElementNumber,
            "h2" => $h2ElementNumber,
            "h3" => $h3ElementNumber,
            "h4" => $h4ElementNumber,
            "h5" => $h5ElementNumber
        ];
        $headingsEncode = $this->jsonHelper->serialize($headingsObject);
        return $headingsEncode;
    }

    /**
     * @param object $dom
     * @param string $tagNumber
     * @return int
     */
    public function getElementTagNumber($dom, $tagNumber)
    {
        $elementObject = $dom->getElementsByTagName($tagNumber);
        $elementNumber = 0;
        foreach ($elementObject as $element) {
            if ($element->nodeValue) {
                $elementNumber++;
            }
        }
        return $elementNumber;
    }

    /**
     * @param object $dom
     * @return int
     */
    public function handleCanonicalTagDOM($dom)
    {
        $canonicalObject = $dom->getElementsByTagName('link');
        $statusCanonicalTag = 0;
        foreach ($canonicalObject as $element) {
            $rel = $element->getAttribute('rel');
            if ($rel === 'canonical') {
                $canonicalTag = $element->getAttribute('href');
                if ($canonicalTag) {
                    $statusCanonicalTag = 1;
                }
            }
        }
        return $statusCanonicalTag;
    }

    /**
     * @param object $dom
     * @return bool
     */
    public function handleOpenGraphDOM($dom)
    {
        $metaObject = $dom->getElementsByTagName('meta');
        $statusOpenGraphTitle = false;
        $statusOpenGraphDescription = false;
        $statusOpenGraphImage = false;
        $statusOpenGraphUrl = false;
        $statusOpenGraphType = false;
        foreach ($metaObject as $element) {
            $property = $element->getAttribute('property');
            $context = $element->getAttribute('content');
            $statusOpenGraphTitle = $this->compareCard($property, 'og:title', $context);
            $statusOpenGraphImage = $this->compareCard($property, 'og:image', $context);
            $statusOpenGraphDescription = $this->compareCard($property, 'og:description', $context);
            $statusOpenGraphUrl = $this->compareCard($property, 'og:url', $context);
            $statusOpenGraphType = $this->compareCard($property, 'og:type', $context);
        }
        return $statusOpenGraphType || $statusOpenGraphDescription || $statusOpenGraphUrl || $statusOpenGraphImage || $statusOpenGraphTitle;
    }

    /**
     * @param object $dom
     * @return bool
     */
    public function handleTwitterCardDOM($dom)
    {
        $statusTwitterCardTitle = false;
        $statusTwitterCardDescription = false;
        $statusTwitterCardSite = false;
        $statusTwitterCardImage = false;
        $metaObject = $dom->getElementsByTagName('meta');
        foreach ($metaObject as $element) {
            $context = $element->getAttribute('content');
            $name = $element->getAttribute('name');
            $statusTwitterCardSite = $this->compareCard($name, 'twitter:site', $context);
            $statusTwitterCardTitle = $this->compareCard($name, 'twitter:title', $context);
            $statusTwitterCardDescription = $this->compareCard($name, 'twitter:description', $context);
            $statusTwitterCardImage = $this->compareCard($name, 'twitter:image', $context);
        }
        return $statusTwitterCardDescription || $statusTwitterCardImage || $statusTwitterCardTitle || $statusTwitterCardSite;
    }

    /**
     * @param string $name
     * @param string $nameCompare
     * @param string $context
     * @return bool
     */
    public function compareCard($name, $nameCompare, $context)
    {
        return $name === $nameCompare && $context !== null && $context !== '';
    }
    /**
     * @param string $dom
     * @return int
     */
    public function handleImagesDOM($dom)
    {
        $imageObject = $dom->getElementsByTagName('img');
        $countLostImagesAlt = 0;
        foreach ($imageObject as $element) {
            $alt = $element->getAttribute('alt');
            if (!$alt || $alt === '' || $alt === null) {
                $countLostImagesAlt++;
            }
        }
        return $countLostImagesAlt;
    }

    /**
     * @param string $url
     * @return bool|string
     */
    public function phpCrawPage($url)
    {
        try {
            $timeout = 60;
            $url = str_replace("&amp;", "&", urldecode(trim($url)));
            $ch = curl_init();
            curl_setopt(
                $ch,
                CURLOPT_USERAGENT,
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1"
            );
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * @param string $url
     * @return string
     */
    public function crawlPage($url)
    {
        $dom = $this->DOMDocument;
        @$dom->loadHTMLFile($url);
        return $dom->saveHTML();
    }

    /**
     * @param string $requestPath
     * @param string $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleUrl($requestPath, $storeId)
    {
        $baseUrl = $this->getBaseUrl($storeId);
        return $baseUrl . $requestPath;
    }

    /**
     * @param string $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl($storeId)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
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
