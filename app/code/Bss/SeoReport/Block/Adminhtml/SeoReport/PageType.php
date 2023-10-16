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
namespace Bss\SeoReport\Block\Adminhtml\SeoReport;

/**
 * Class PageType
 * @package Bss\SeoReport\Block\Adminhtml\SeoReport
 */
class PageType extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\SeoReport\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;

    /**
     * @var \Bss\SeoReport\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * PageType constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\SeoReport\Helper\Data $helperData
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param \Bss\SeoReport\Model\UrlRewriteFactory $urlRewriteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Bss\SeoReport\Helper\Data $helperData,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        \Bss\SeoReport\Model\UrlRewriteFactory $urlRewriteFactory,
        array $data = []
    ) {
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->helperData = $helperData;
        $this->storeManager = $context->getStoreManager();
        $this->coreRegistry = $registry;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Bss\SeoReport\Helper\Data
     */
    public function getDataHelper()
    {
        return $this->helperData;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        $productId = $this->getRequest()->getParam('id');
        if ($productId) {
            return $this->coreRegistry->registry('current_product');
        } else {
            return false;
        }
    }

    /**
     * @return bool|mixed
     */
    public function getCategory()
    {
        $categoryId = $this->getRequest()->getParam('id');
        if ($categoryId) {
            return $this->coreRegistry->registry('current_category');
        } else {
            return false;
        }
    }

    /**
     * @return bool|mixed
     */
    public function getCmsPage()
    {
        $cmsPageId = $this->getRequest()->getParam('page_id');
        if ($cmsPageId) {
            return $this->coreRegistry->registry('cms_page');
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        return [
            "meta_title" => "",
            "meta_description" => "",
            "meta_keyword" => "",
            "main_keyword" => "",
            "url_key" => "",
            "description" => ""
        ];
    }

    /**
     * @param object product
     * @return object|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllCategory($product)
    {
        $categoryIds = $product->getCategoryIds();
        if ($categoryIds == []) {
            return null;
        }

        $categories = $this->getCategoryCollection()
            ->addAttributeToFilter('entity_id', $categoryIds);
        $maxs = null;
        $myCategory = null;
        foreach ($categories as $key => $category) {
            $maxs = $key;
        }
        foreach ($categories as $key => $category) {
            if ($key == $maxs) {
                $myCategory = $category;
            }
        }
        return $myCategory;
    }

    /**
     * Get canonical url key
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getCanonicalUrlKey($product)
    {
        return $this->productUrlPathGenerator->getUrlKey($product);
    }
    /**
     * Get canonical url path
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getCanonicalUrlPath($product, $category = null)
    {
        return $this->productUrlPathGenerator->getUrlPath($product, $category);
    }

    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool $level
     * @param bool $sortBy
     * @param bool $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    /**
     * @param string $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl($storeId = '')
    {
        if ($storeId) {
            return $this->storeManager->getStore($storeId)->getBaseUrl();
        } else {
            return $this->storeManager->getStore($this->getStoreId())->getBaseUrl();
        }

    }

    /**
     * @param object $object
     * @param string $type
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDataCrawl($object, $type)
    {
        $currentTime = time();
        $dataObject = [];
        $collection = $this->urlRewriteFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', $object->getId())
            ->addFieldToFilter('entity_type', $type);
        if ($collection->getSize()) {
            foreach ($collection as $urlItem) {
                $expiredTime = (int)$urlItem->getExpired();
                if ($expiredTime < $currentTime) {
                    $storeId = $urlItem->getStoreId();
                    $dataToAdd = [
                        'path' => $urlItem->getRequestPath(),
                        'main_url' => $this->getBaseUrl($storeId)
                    ];
                    $dataObject[] = $dataToAdd;
                }
            }
        }
        return $dataObject;
    }

    /**
     * @return string
     */
    public function getLinkCrawl()
    {
        return $this->getUrl('seo_report/crawl/crawl');
    }

    /**
     * @return string
     */
    public function getSearchConsoleUrl()
    {
        return $this->getUrl('seo_report/google/console');
    }

    /**
     * @return string
     */
    public function getSettingUrl()
    {
        return $this->getUrl('admin/system_config/edit/section/bss_seo_report');
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if ((int)$this->storeManager->getStore()->getId() === 0) {
            return $this->storeManager->getDefaultStoreView()
                ->getStoreId();
        } else {
            return $this->storeManager->getStore()->getId();
        }
    }

    /**
     * @param string $metaTitle
     * @return array
     */
    public function getTitleInfo($metaTitle)
    {
        $colorReturn = 'red';
        $percentReturn = 0;

        $titleLength = mb_strlen($metaTitle, 'UTF-8');
        if ($titleLength < 50) {
            $colorReturn = 'orange';
            $percentReturn = (int)(($titleLength/70)*100);
        }
        if ($titleLength >= 50 && $titleLength <= 70) {
            $colorReturn = 'green';
            $percentReturn = (int)(($titleLength/70)*100);
        }
        if ($titleLength > 70) {
            $colorReturn = 'red';
            $percentReturn = 100;
        }
        return [
            'color' => $colorReturn,
            'percent' => $percentReturn
        ];
    }

    /**
     * @param string $metaDescription
     * @return array
     */
    public function getDescriptionInfo($metaDescription)
    {
        $colorReturn = 'red';
        $percentReturn = 0;

        $descriptionLength = mb_strlen($metaDescription, 'UTF-8');
        if ($descriptionLength < 100) {
            $colorReturn = 'red';
            $percentReturn = (int)(($descriptionLength/255)*100);
        }
        if ($descriptionLength >= 100 && $descriptionLength < 200) {
            $colorReturn = 'orange';
            $percentReturn = (int)(($descriptionLength/255)*100);
        }
        if ($descriptionLength >= 200 && $descriptionLength <= 255) {
            $colorReturn = 'green';
            $percentReturn = (int)(($descriptionLength/255)*100);
        }
        if ($descriptionLength > 255) {
            $colorReturn = 'red';
            $percentReturn = 100;
        }
        return [
            'color' => $colorReturn,
            'percent' => $percentReturn
        ];
    }
}
