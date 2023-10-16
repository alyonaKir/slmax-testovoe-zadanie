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
 * @package    Bss_HrefLang
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HrefLang\Block;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\View\Element\Template;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class HrefLang
 * @package Bss\HrefLang\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HrefLang extends Template
{
    const CMS = 'cms-page';
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var array
     */
    protected $cms = [];
    /**
     * @var \Bss\HrefLang\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;
    /**
     * @var \Bss\HrefLang\Model\ResourceModel\ConnectionDB
     */
    private $connectionDB;
    /**
     * @var \Magento\Cms\Model\Page
     */
    private $page;

    /**
     * HrefLang constructor.
     * @param Template\Context $context
     * @param \Bss\HrefLang\Helper\Data $dataHelper
     * @param \Magento\Catalog\Model\CategoryFactory $categoryRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Cms\Model\Page $page
     * @param \Bss\HrefLang\Model\ResourceModel\ConnectionDB $connectionDB
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\HrefLang\Helper\Data $dataHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Cms\Model\Page $page,
        \Bss\HrefLang\Model\ResourceModel\ConnectionDB $connectionDB,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->page = $page;
        $this->connectionDB = $connectionDB;
        $this->urlFinder = $urlFinder;
        parent::__construct($context, $data);
    }

    /**
     * @param int $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrlHreflang($storeId)
    {
        return $this->processUrl($this->_storeManager->getStore($storeId)->getCurrentUrl(false));
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return \Bss\HrefLang\Helper\Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    /**
     * @return string
     */
    public function getFullActionName()
    {
        return $this->getRequest()->getFullActionName();
    }

    /**
     * @param string $type
     * @param int $storeId
     * @return bool|string
     */
    public function getCategoryUrlHreflang($type, $storeId)
    {
        $categoryId = $this->getCurrentId($type);
        try {
            $category = $this->categoryRepository->create()->setStoreId($storeId)->load($categoryId);
            if ($category->getIsActive() != 1) {
                return false;
            }
            $requestPath = $this->getCategoryRequestPath($categoryId, $storeId);
            if ($requestPath) {
                $url = $this->_storeManager->getStore($storeId)->getUrl($requestPath, ['_current' => false]);
                $categoryUrl = $url . $this->insertParamsToHreflang();
                return $this->processUrl($categoryUrl);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $type
     * @param int $storeId
     * @param int $websiteId
     * @return bool|string
     */
    public function getProductUrlHreflang($type, $storeId, $websiteId)
    {
        $productId = $this->getCurrentId($type);
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
            $productWebsites = $product->getWebsiteIds();
            if ((int)$product->getStatus() !== 1) {
                return false;
            }
            if (!in_array($websiteId, $productWebsites)) {
                return false;
            }
            $productUrl = $product->setStoreId($storeId)->getUrlModel()->getUrlInStore($product, ['_escape' => true])
                . $this->insertParamsToHreflang();
            return $this->processUrl($productUrl);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param int $storeId
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCmsPageUrlHreflang($storeId)
    {
        $currentCmsPage = $this->getCurrentCmsPage();
        if (!empty($currentCmsPage)) {
            if (in_array(0, $currentCmsPage['store_id']) || in_array($storeId, $currentCmsPage['store_id'])) {
                $storeCmsPage = $this->connectionDB->getCms($storeId, $currentCmsPage['id'], false);
                if (empty($storeCmsPage)) {
                    $storeCmsPage = $this->connectionDB->getCms(false, $currentCmsPage['id'], false);
                }
                if (empty($storeCmsPage)) {
                    return false;
                }
                $storeCmsPageUrl = $storeCmsPage['request_path'];
            } else {
                $storeCmsPage = $this->connectionDB->getCms($storeId, false, $currentCmsPage['ident']);
                if (empty($storeCmsPage) || $storeCmsPage['is_active'] == 0) {
                    return false;
                }
                $storeCmsPageUrl = $storeCmsPage['request_path'];
            }
            $cmsUrl = $this->_storeManager->getStore($storeId)->getUrl($storeCmsPageUrl, ['_current' => false])
                . $this->insertParamsToHreflang();
            return $this->processUrl($cmsUrl);
        }
        return false;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function processUrl($url)
    {
        if (!$url || $url === '' || $url === null) {
            return '';
        }
        $urlObject = parse_url($url);
        $hostUrl = $urlObject['host'];
        $pathUrl = $urlObject['path'];
        $schemeUrl = $urlObject['scheme'];
        $queryUrl = (isset($urlObject['query'])) ? $urlObject['query'] : '';

        $pathUrl = rtrim($pathUrl, '/');

        //Process Query, Remove SID
        $queryAfter = '';
        if ($queryUrl) {
            $queryObject = explode('&', $queryUrl);
            foreach ($queryObject as $key => $value) {
                if (strpos($value, 'SID') !== false) {
                    unset($queryObject[$key]);
                }
            }
            $queryAfter = implode('&', $queryObject);
            $queryStringAdd = ($queryAfter !== '') ? '?' : '';
            $queryAfter = $queryStringAdd . $queryAfter;
        }
        return $schemeUrl . '://' . $hostUrl . $pathUrl . $queryAfter;
    }

    /**
     * @return string
     */
    protected function insertParamsToHreflang()
    {
        $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $currentUrl = $this->processUrl($currentUrl);
        $urlObject = parse_url($currentUrl);
        $queryUrl = (isset($urlObject['query'])) ? $urlObject['query'] : '';
        if ($queryUrl !== '' && $queryUrl !== null) {
            $queryUrl = '?' . $queryUrl;
        }
        return $queryUrl;
    }

    /**
     * @return array
     */
    protected function getCurrentCmsPage()
    {
        if (!empty($this->cms)) {
            return $this->cms;
        }
        if ($this->page->getId()) {
            $this->cms['id'] = $this->page->getId();
            $this->cms['ident'] = $this->page->getIdentifier();
            $this->cms['store_id'] = $this->page->getStoreId();
        }
        return $this->cms;
    }

    /**
     * @param string $type
     * @return int
     */
    public function getCurrentId($type)
    {
        if ($this->id != 0) {
            return $this->id;
        }
        $data = $this->registry->registry($type);
        $this->id = $data->getId();
        return $this->id;
    }

    /**
     * @param int $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreFromId($storeId)
    {
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHrefLang()
    {
        //Url For HREFLANG
        $storeId = $this->getStoreId();
        $hreflangStore = $this->dataHelper->getStoreHreflang($storeId);
        $hreflangTag = [];
        $handle = $this->getFullActionName();

        if (is_array($hreflangStore)) {
            foreach ($hreflangStore as $value) {
                $country = strtolower($value['country']);
                $store = $this->getStoreFromId($value['store']);
                if (!$store->isActive()) {
                    continue;
                }
                $websiteId = $store->getWebsiteId();
                if ($handle == 'catalog_category_view') {
                    $hreflangUrl = $this->getCategoryUrlHreflang('current_category', $value['store']);
                } elseif ($handle == 'catalog_product_view') {
                    $hreflangUrl = $this->getProductUrlHreflang('current_product', $value['store'], $websiteId);
                } elseif ($handle == 'cms_page_view') {
                    $hreflangUrl = $this->getCmsPageUrlHreflang($value['store']);
                } else {
                    $hreflangUrl = $this->getCurrentUrlHreflang($value['store']);
                }

                if (!$hreflangUrl) {
                    continue;
                }

                if ($country == 'not_assign') {
                    $hreflangTag[] =
                        '<link rel="alternate" href="' . $hreflangUrl
                        . '" hreflang="' . $value['language'] . '" />' . PHP_EOL;
                } else {
                    $hreflangTag[] =
                        '<link rel="alternate" href="'
                        . $hreflangUrl . '" hreflang="'
                        . $value['language'] . '-' . $country . '" />' . PHP_EOL;
                }
            }
        }
        return $hreflangTag;
    }

    /**
     * @param string $categoryId
     * @param int $storeId
     * @return bool|string
     */
    protected function getCategoryRequestPath($categoryId, $storeId)
    {
        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::ENTITY_ID => $categoryId,
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $storeId,
        ]);
        if ($rewrite) {
            return $rewrite->getRequestPath();
        }
        return false;
    }
}
