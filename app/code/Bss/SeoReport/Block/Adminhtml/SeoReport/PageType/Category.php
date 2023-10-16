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

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class ReportField
 * @package Bss\SeoReport\Block\Adminhtml\Catalog\Product\Form
 */
class Category extends \Bss\SeoReport\Block\Adminhtml\SeoReport\PageType
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * Category constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\SeoReport\Helper\Data $helperData
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlFinderInterface $urlFinder
     * @param \Bss\SeoReport\Model\UrlRewriteFactory $urlRewriteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Bss\SeoReport\Helper\Data $helperData,
        \Magento\Framework\UrlInterface $url,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        UrlFinderInterface $urlFinder,
        \Bss\SeoReport\Model\UrlRewriteFactory $urlRewriteFactory,
        array $data = []
    ) {
        $this->url = $url;
        $this->urlFinder = $urlFinder;
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPageUrl()
    {
        $categoryObject = $this->getCategory();
        if ($categoryObject) {
            return $this->getCategoryUrl($categoryObject->getId());
        } else {
            return "";
        }
    }

    /**
     * @param array $object
     * @param string $type
     * @return array
     */
    public function getDataCrawl($object = [], $type = '')
    {
        $object = $this->getCategory();
        $type = 'category';
        if (!$object || !$object->getId()) {
            return [];
        }
        return parent::getDataCrawl($object, $type);
    }

    /**
     * Retrieve URL instance
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlInstance()
    {
        return $this->url;
    }

    /**
     * @param string $categoryId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryUrl($categoryId)
    {
        $storeId = $this->getStoreId();
        if ((int)$storeId === 0) {
            //Get Store Default
            $storeId =  $this->storeManager->getDefaultStoreView()->getStoreId();
        }
        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::ENTITY_ID => $categoryId,
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $storeId,
        ]);
        if ($rewrite) {
            $categoryPath = $rewrite->getRequestPath();
            $baseUrl = $this->getBaseUrl();
            return $baseUrl . $categoryPath;
        }
        return '';
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        $categoryObject = $this->getCategory();
        if (!$categoryObject) {
            return parent::getMetaData();
        } else {
            $metaTitle = $categoryObject->getData('meta_title');
            $metaKeyword = $categoryObject->getData('meta_keywords');
            $metaDescription = $categoryObject->getData('meta_description');
            $mainKeyword = $categoryObject->getData('main_keyword');
            $urlKey = $categoryObject->getData('url_key');
            $description = $categoryObject->getData('description');
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
     * @param string $metaTitle
     * @return array
     */
    public function getTitleInfo($metaTitle = '')
    {
        $categoryObject = $this->getCategory();
        if (!$categoryObject) {
            return [
                'color' => "red",
                'percent' => 0
            ];
        }
        $metaTitle = $categoryObject->getData("meta_title");
        return parent::getTitleInfo($metaTitle);
    }

    /**
     * @param string $metaDescription
     * @return array
     */
    public function getDescriptionInfo($metaDescription = '')
    {
        $categoryObject = $this->getCategory();
        if (!$categoryObject) {
            return [
                'color' => "red",
                'percent' => 0
            ];
        }
        $metaDescription = $categoryObject->getData("meta_description");
        return parent::getDescriptionInfo($metaDescription);
    }
}
