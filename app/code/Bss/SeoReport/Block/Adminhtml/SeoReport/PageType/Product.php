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
namespace Bss\SeoReport\Block\Adminhtml\SeoReport\PageType;

/**
 * Class Product
 * @package Bss\SeoReport\Block\Adminhtml\SeoReport\PageType
 */
class Product extends \Bss\SeoReport\Block\Adminhtml\SeoReport\PageType
{
    /**
     * @var \Bss\CanonicalTag\Helper\Data
     */
    private $canonicalTagHelper;
    /**
     * @var \Bss\CanonicalTag\Helper\ProductData
     */
    private $canonicalTagData;

    /**
     * Product constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\SeoReport\Helper\Data $helperData
     * @param \Bss\CanonicalTag\Helper\Data $canonicalTagHelper
     * @param \Bss\CanonicalTag\Helper\ProductData $canonicalTagData
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param \Bss\SeoReport\Model\UrlRewriteFactory $urlRewriteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Bss\SeoReport\Helper\Data $helperData,
        \Bss\CanonicalTag\Helper\Data $canonicalTagHelper,
        \Bss\CanonicalTag\Helper\ProductData $canonicalTagData,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        \Bss\SeoReport\Model\UrlRewriteFactory $urlRewriteFactory,
        array $data = []
    ) {
        $this->canonicalTagHelper = $canonicalTagHelper;
        $this->canonicalTagData = $canonicalTagData;
        parent::__construct(
            $context,
            $registry,
            $helperData,
            $categoryCollectionFactory,
            $productUrlPathGenerator,
            $urlRewriteFactory,
            $data
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPageUrl()
    {
        $productObject = $this->getProduct();
        if (!$productObject) {
            return "";
        }

        //Get Base Url
        $currentUrl = $this->getBaseUrl();
        $currentUrl = rtrim($currentUrl, '/');
        $currentUrl = $currentUrl . '/';

        //Get Product URL
        $canonicalTagHelper = $this->canonicalTagHelper;
        $dataHelper = $this->getDataHelper();

        $canonicalEnable = $canonicalTagHelper->isCanonicalEnable($this->getStoreId());
        $canonicalPath = $canonicalTagHelper->getProductPath($this->getStoreId());
        $productSuffix = $dataHelper->getProductSuffixUrl($this->getStoreId());
        $seoCanonicalTag = $productObject->getData('seo_canonical_tag');

        if ($canonicalPath === 'long' && $canonicalEnable) {
            $allCategory = $this->getAllCategory($productObject);
            $categoryPath = $this->getCanonicalUrlPath($productObject, $allCategory);
            $productUrl = $currentUrl . $categoryPath . $productSuffix;
        } else {
            $productPath = $this->getCanonicalUrlKey($productObject);
            $productUrl = $currentUrl . $productPath . $productSuffix;
        }
        $canonicalTagUrl = $productUrl;
        if ($seoCanonicalTag !== '' && $seoCanonicalTag !== null && $seoCanonicalTag && $canonicalEnable) {
            $canonicalTagUrl = $seoCanonicalTag;
        }
        return $canonicalTagUrl;
    }

    /**
     * @param array $selectingData
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultUrl($selectingData)
    {
        $defaultUrl = '';
        foreach ($selectingData as $value) {
            if ((int)$value['store'] === 0) {
                $defaultUrl = $value['url_value'];
            }
        }
        foreach ($selectingData as $myValue) {
            if (isset($myValue['url_value']) && $this->getStoreId() == $myValue['store']) {
                $defaultUrl = $myValue['url_value'];
            }
        }
        return $defaultUrl;
    }
    /**
     * @return array
     */
    public function getMetaData()
    {
        $productObject = $this->getProduct();
        if (!$productObject) {
            return parent::getMetaData();
        } else {
            $metaTitle = $productObject->getData('meta_title');
            $metaKeyword = $productObject->getData('meta_keyword');
            $metaDescription = $productObject->getData('meta_description');
            $mainKeyword = $productObject->getData('main_keyword');
            $urlKey = $productObject->getData('url_key');
            $description = $productObject->getData('description');
        }
        return [
            "meta_title" => ($metaTitle) ? $metaTitle : '',
            "meta_description" => ($metaDescription) ? $metaDescription : '',
            "meta_keyword" => ($metaKeyword) ? $metaKeyword : '',
            "main_keyword" => ($mainKeyword) ? $mainKeyword : '',
            "url_key" => ($urlKey) ? $urlKey : '',
            "description" => ($description) ? $description : '',
        ];
    }

    /**
     * @param array $object
     * @param string $type
     * @return array
     */
    public function getDataCrawl($object = [], $type = '')
    {
        $object = $this->getProduct();
        $type = 'product';
        if (!$object || !$object->getId()) {
            return [];
        }
        return parent::getDataCrawl($object, $type);
    }

    /**
     * @param string $metaTitle
     * @return array
     */
    public function getTitleInfo($metaTitle = '')
    {
        $product = $this->getProduct();
        if (!$product) {
            return [
                'color' => "red",
                'percent' => 0
            ];
        }
        $metaTitle = $product->getData("meta_title");
        return parent::getTitleInfo($metaTitle);
    }

    /**
     * @param string $metaDescription
     * @return array
     */
    public function getDescriptionInfo($metaDescription = '')
    {
        $product = $this->getProduct();
        if (!$product) {
            return [
                'color' => "red",
                'percent' => 0
            ];
        }
        $metaDescription = $product->getData("meta_description");
        return parent::getDescriptionInfo($metaDescription);
    }
}
