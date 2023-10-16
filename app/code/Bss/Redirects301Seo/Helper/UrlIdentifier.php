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
 * @package    Bss_Redirects301Seo
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Redirects301Seo\Helper;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class UrlIdentifier
 *
 * @package Bss\Redirects301Seo\Helper
 */
class UrlIdentifier extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\Redirects301Seo\Model\SelectUrlDeletedFactory
     */
    private $selectUrlDeleted;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var SaveUrlKeyDeleted
     */
    protected $saveUrlKeyDeleted;
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * UrlIdentifier constructor.
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Bss\Redirects301Seo\Model\SelectUrlDeletedFactory $selectUrlDeleted
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param SaveUrlKeyDeleted $saveUrlKeyDeleted
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Bss\Redirects301Seo\Model\SelectUrlDeletedFactory $selectUrlDeleted,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        SaveUrlKeyDeleted $saveUrlKeyDeleted,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->urlFinder = $urlFinder;
        $this->categoryRepository = $categoryRepository;
        $this->responseFactory = $responseFactory;
        $this->selectUrlDeleted = $selectUrlDeleted;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->saveUrlKeyDeleted =  $saveUrlKeyDeleted;
    }

    /**
     * Get delete url by product Id
     *
     * @param int $productId
     * @return array
     */
    public function getDeletedUrlByProductId($productId)
    {
        $collection = $this->selectUrlDeleted->create()
            ->getCollection()
            ->addFieldToFilter('product_id', $productId);
        return $collection->getData();
    }

    /**
     * Get Url by path
     *
     * @param string $path
     * @return mixed
     */
    public function getDeletedUrlByPath($path)
    {
        $collection = $this->selectUrlDeleted->create()
            ->getCollection()
            ->addFieldToFilter('url_deleted', $path);
        return $collection->getData();
    }

    /**
     * Get store manager
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }


    /**
     * @param string $categoryId
     * @param bool $isPriority
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryUrl($categoryId, $isPriority = false)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $category = $this->categoryRepository->get($categoryId, $storeId);
        if ($category->getIsActive()) {
            if ($isPriority) {
                $categoryPriorityId = $category->getData('priority_id');
                if ($categoryPriorityId) {
                    //Get category Priority
                    $categoryPriority = $this->categoryRepository->get($categoryPriorityId, $storeId);
                    //Check Category Enable
                    if ($categoryPriority->getIsActive() && ($categoryPriority->getLevel() >= 2)) {
                        return $this->getCategoryUrlFormId($categoryPriority->getId());
                    } else {
                        return $this->getCategoryUrlFormId($category->getId());
                    }
                } else {
                    return $this->getCategoryUrlFormId($category->getId());
                }
            } else {
                return $this->getCategoryUrlFormId($category->getId());
            }
        } else {
            return '';
        }
    }

    /**
     * @param string $categoryId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryUrlFormId($categoryId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::ENTITY_ID => $categoryId,
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $storeId,
        ]);
        if ($rewrite) {
            $categoryPath = $rewrite->getRequestPath();
            $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
            return $baseUrl . $categoryPath;
        }
        return '';
    }

    /**
     * Check redirect day
     *
     * @param string $redirectDay
     * @param array $dataRedirects
     * @return bool
     */
    public function checkRedirectDay($redirectDay, $dataRedirects)
    {
        $checkStatusDay = true;
        if ($redirectDay != null && !$dataRedirects) {
            //Handle Redirect Day
            $redirectDay = (float)$redirectDay;

            //Get CurrentTime
            $currentTime = $this->date->gmtDate();
            $currentTime = date($currentTime);
            $currentTime = strtotime($currentTime);

            foreach ($dataRedirects as $dataRedirect) {
                $dateDeleted = $currentTime - strtotime($dataRedirect['update_at']);
                $dateDeleted = $dateDeleted/86400;
                $dateDeleted = (float)$dateDeleted;
                if ($dateDeleted > $redirectDay) {
                    $this->saveUrlKeyDeleted->deleteUrlValue($dataRedirect['product_id']);
                    $checkStatusDay = false;
                    break;
                }
            }
        }

        return $checkStatusDay;
    }

    /**
     * @param string $url
     * @param string $productSuffixUrl
     * @param null $redirectDay
     * @return mixed
     */
    public function readUrl($url, $productSuffixUrl = '.html', $redirectDay = null)
    {
        $url = ltrim($url, '/');
        $result['status'] = null;
        $result['path'] = null;
        $result['categories'] = null;
        $urlIdAndCategory = substr($url, 0, 20);

        if ($urlIdAndCategory == 'catalog/product/view') {
            $idAndCategory = strstr($url, 'id');
            $idAndCategory = explode('/', $idAndCategory);

            $urlObject = $this->getDeletedUrlByProductId($idAndCategory[1]);
            if ($urlObject && $this->checkRedirectDay($redirectDay, $urlObject)) {
                $result['status'] = true;
                $result['path'] = $urlObject[0]['url_deleted'];
                $result['categories'] = $urlObject[0]['categories_id'];
            }
        } else {
            $url = str_replace($productSuffixUrl, '', $url);
            $finalUrl = $this->processUrl($url);
            $urlObject = $this->getDeletedUrlByPath($finalUrl);

            if ($urlObject && $this->checkRedirectDay($redirectDay, $urlObject)) {
                $result['status'] = true;
                $result['path'] = $urlObject[0]['url_deleted'];
                $result['categories'] = $urlObject[0]['categories_id'];
            }
        }

        return $result;
    }

    /**
     * @param string $url
     * @return string
     */
    public function processUrl($url)
    {
        $urlArray = explode('/', $url);
        if (!empty($urlArray)) {
            $urlFinal = '';
            foreach ($urlArray as $urlString) {
                $urlFinal = $urlString;
            }
            return $urlFinal;
        } else {
            return $url;
        }
    }
}
