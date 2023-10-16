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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Helper;

use Bss\MetaTagManager\Model\MetaTemplateFactory;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;


/**
 * Class ProcessMetaTemplate
 * @package Bss\MetaTagManager\Helper
 */
class ProcessMetaTemplate
{
    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var MetaTemplateFactory
     */
    private $metaTemplateFactory;
    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;
    /**
     * @var ProcessSaveEntity
     */
    private $processSaveEntity;
    /**
     * @var object
     */
    protected $categoryTemplateCollection;

    /**
     * ProcessMetaTemplate constructor.
     * @param Data $dataHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param MetaTemplateFactory $metaTemplateFactory
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param ProcessSaveEntity $processSaveEntity
     */
    public function __construct(
        \Bss\MetaTagManager\Helper\Data $dataHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Bss\MetaTagManager\Helper\ProcessSaveEntity $processSaveEntity
    ) {
        $this->processSaveEntity = $processSaveEntity;
        $this->urlFinder = $urlFinder;
        $this->metaTemplateFactory = $metaTemplateFactory;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param object $object
     * @param object $template
     * @param string $type
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getMetaData($object, $template, $type)
    {
        $dataReturn = [
            'meta_title' => $this->handleMetaTitle($object, $template, $type),
            'meta_description' => $this->handleMetaDescription($object, $template, $type),
            'meta_keyword' => $this->handleMetaKeywords($object, $template, $type),
            'url_key' => $this->handleUrlKey($object, $template, $type),
            'description' => $this->handleDescription($object, $template, $type),
            'short_description' => $this->handleShortDescription($object, $template, $type)
        ];
        return $dataReturn;
    }

    /**
     * @param object $product
     * @param object $template
     * @return $this
     * @throws Exception
     */
    public function processProductMeta($product, $template)
    {
        if (!$this->dataHelper->isActiveBssMetaTag()) {
            return $this;
        }
        $metaData = $this->getMetaData($product, $template, 'product');
        $this->processSaveEntity->handleProductMeta($product, $metaData);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryCollection()
    {
        if ($this->categoryTemplateCollection) {
            return $this->categoryTemplateCollection;
        } else {
            $collection = $this->metaTemplateFactory->create()
                ->getCollection()
                ->addFieldToFilter('meta_type', 'category')
                ->addFieldToFilter('status', '1');
            return $collection;
        }
    }

    /**
     * @param string $categoryId
     * @param object $template
     * @return $this
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function processCategoryMeta($categoryId, $template)
    {
        $category = $this->categoryRepository->get($categoryId, 0);
        if ($this->checkCategoryTemplate($category, $template)) {
            $this->handleCategoryMeta($category, $template);
        }

        $isUseSubcategory = (int)$template->getUseSub();
        if ($isUseSubcategory) {
            $childCategoryArray = $this->getChildCategories($category);
            if (!empty($childCategoryArray)) {
                foreach ($childCategoryArray as $childCategoryId) {
                    //Check in Ignore
                    $childCategoryDefault = $this->categoryRepository->get($childCategoryId, 0);
                    $excludedMetaTemplate = $childCategoryDefault->getData('excluded_meta_template');
                    if ($excludedMetaTemplate !== '1' && $this->checkCategoryTemplate($childCategoryDefault, $template)) {
                        $this->handleCategoryMeta($childCategoryDefault, $template);
                    }
                }
            }
        }
        //Get All Category and Child Category
        $this->processCategoryStoreView(
            $template,
            $categoryId,
            $isUseSubcategory
        );
        return $this;
    }

    /**
     * @param object $category
     * @param object $template
     * @return bool
     */
    public function checkCategoryTemplate($category, $template)
    {
        $parentCategories = $category->getParentIds();
        if (empty($parentCategories)) {
            $parentCategories = [];
        }
        $parentCategories[] = $category->getId();
        //Get Category Template collection and Compare
        $collection = $this->getCategoryCollection();
        if ($collection->getSize()) {
            $finalTemplate = [];
            $maxPriority = 0;
            foreach ($collection as $metaObject) {
                $currentStoreView = $category->getStoreId();
                $statusCategoryTemplate = $this->isCategoryTemplate(
                    $metaObject,
                    $parentCategories,
                    $currentStoreView,
                    $category->getId()
                );
                if (!$statusCategoryTemplate) {
                    continue;
                }
                //HandleData
                $priority = $metaObject->getPriority();
                if ((int)$priority >= $maxPriority) {
                    $finalTemplate = $metaObject;
                    $maxPriority = (int)$priority;
                }
            }

            $excludedMetaTemplate = $category->getData('excluded_meta_template');
            if ($excludedMetaTemplate !== '1' && (int)$finalTemplate->getId() === (int)$template->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param object $template
     * @param string $categoryId
     * @param bool $isUseSubcategory
     * @return $this
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function processCategoryStoreView(
        $template,
        $categoryId,
        $isUseSubcategory
    ) {
        //Make for Store View
        $stores = $this->dataHelper->getStoreManager()->getStores(false);
        $templateStore = $template->getStore();
        $templateStoreArray = explode(',', $templateStore);
        foreach ($stores as $store) {
            //Get Category in Other Store
            $storeId = $store->getId();
            if (!in_array($storeId, $templateStoreArray)) {
                continue;
            }

            $categoryStore = $this->categoryRepository->get($categoryId, $storeId);
            $excludedMetaTemplate = $categoryStore->getData('excluded_meta_template');
            if ($excludedMetaTemplate !== '1' && $this->checkCategoryTemplate($categoryStore, $template)) {
                $this->handleCategoryMeta($categoryStore, $template);
            }
            $this->processCategoryStoreViewChild(
                $isUseSubcategory,
                $categoryStore,
                $template,
                $storeId
            );
        }
        return $this;
    }

    /**
     * @param bool $isUseSubcategory
     * @param object $categoryStore
     * @param object $template
     * @param string $storeId
     * @return $this
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function processCategoryStoreViewChild(
        $isUseSubcategory,
        $categoryStore,
        $template,
        $storeId
    ) {
        if ($isUseSubcategory) {
            $childCategoryArray = $this->getChildCategories($categoryStore);
            if (!empty($childCategoryArray)) {
                foreach ($childCategoryArray as $childCategoryId) {
                    $childCategoryDefault = $this->categoryRepository->get($childCategoryId, $storeId);
                    $excludedMetaTemplate = $childCategoryDefault->getData('excluded_meta_template');
                    if ($excludedMetaTemplate !== '1' && $this->checkCategoryTemplate($childCategoryDefault, $template)) {
                        $this->handleCategoryMeta($childCategoryDefault, $template);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param object $category
     * @return array
     */
    public function getChildCategories($category)
    {
        $arrayInput = [];
        $childCategories = $category->getChildrenCategories();
        if (!empty($childCategories)) {
            foreach ($childCategories as $childCategory) {
                $arrayInput[] = $childCategory->getId();
                $childOfChildCategory = $this->getChildCategories($childCategory);
                $arrayInput = array_merge($arrayInput, $childOfChildCategory);
            }
        }
        return $arrayInput;
    }

    /**
     * @param object $metaObject
     * @param array $parentCategories
     * @param string $storeId
     * @param string $categoryId
     * @return bool
     */
    public function isCategoryTemplate($metaObject, $parentCategories, $storeId, $categoryId)
    {
        $templateCategories = $metaObject->getCategory();
        $storeTemplate = $metaObject->getStore();
        if ($templateCategories && $storeTemplate) {
            $templateCategoryObject = explode(',', $templateCategories);
            $storeTemplateObject = explode(',', $storeTemplate);
            if ((int)$storeId && !in_array($storeId, $storeTemplateObject)) {
                return false;
            }

            $statusReturn = $this->isStatusTemplate(
                $templateCategoryObject,
                $parentCategories,
                $metaObject,
                $categoryId
            );
            return $statusReturn;
        } else {
            return false;
        }
    }

    /**
     * @param array $templateCategoryObject
     * @param array $parentCategories
     * @param object $metaObject
     * @param string $categoryId
     * @return bool
     */
    public function isStatusTemplate($templateCategoryObject, $parentCategories, $metaObject, $categoryId)
    {
        $statusReturn = false;
        foreach ($templateCategoryObject as $categoryTemplateId) {
            if (in_array($categoryTemplateId, $parentCategories) && (int)$metaObject->getUseSub()) {
                $statusReturn = true;
            }
            if ((int)$categoryId === (int)$categoryTemplateId && (int)$metaObject->getUseSub() === 0) {
                $statusReturn = true;
            }
        }
        return $statusReturn;
    }

    /**
     * @param object $category
     * @param object $template
     * @return ProcessSaveEntity
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function handleCategoryMeta($category, $template)
    {
        $metaData = $this->getMetaData($category, $template, 'category');
        return $this->processSaveEntity->handleCategoryMeta($category, $metaData);
    }

    /**
     * @param object $object
     * @param object $template
     * @param string $type
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function handleMetaTitle($object, $template, $type = 'product') : string
    {
        if ($type === 'product') {
            $metaTitle = $this->dataHelper->convertVar($object, $template->getMetaTitle());
        } else {
            $metaTitle = $this->dataHelper->convertCategoryVar($object, $template->getMetaTitle());
        }
        $metaTitle = chop($metaTitle);
        $metaTitle = preg_replace("/\r\n|\r|\n/", ' ', $metaTitle);
        $metaTitle = preg_replace('/[\s]+/mu', ' ', $metaTitle);

        $storeId = $object->getStoreId();
        $maxMetaTitle = $this->dataHelper->getConfig('bss_metatagmanager/' . $type . '/meta_title_length', $storeId);
        $metaTitle = strip_tags($this->dataHelper->truncateString($metaTitle, $maxMetaTitle));

        $metaTitle = trim($metaTitle);
        $metaTitle = rtrim($metaTitle, ",");
        $metaTitle = (string)$metaTitle;
        return $metaTitle;
    }

    /**
     * @param object $object
     * @param object $template
     * @param string $type
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function handleMetaDescription($object, $template, $type = 'product') : string
    {
        if ($type === 'product') {
            $metaDescription = $this->dataHelper->convertVar($object, $template->getMetaDescription());
        } else {
            $metaDescription = $this->dataHelper->convertCategoryVar($object, $template->getMetaDescription());
        }
        $metaDescription = chop($metaDescription);

        $metaDescription = preg_replace("/\r\n|\r|\n/", ' ', $metaDescription);
        $metaDescription = preg_replace('/[\s]+/mu', ' ', $metaDescription);

        $storeId = $object->getStoreId();
        $maxMetaDescription = $this->dataHelper->getConfig('bss_metatagmanager/' . $type . '/meta_description_length', $storeId);

        $metaDescription = strip_tags($this->dataHelper->truncateString($metaDescription, $maxMetaDescription));

        $metaDescription = trim($metaDescription);
        $metaDescription = rtrim($metaDescription, ",");
        $metaDescription = (string)$metaDescription;
        return $metaDescription;
    }

    /**
     * @param object $object
     * @param object $template
     * @param string $type
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function handleMetaKeywords($object, $template, $type = 'product') : string
    {
        if ($type === 'product') {
            $metaKeyword = $this->dataHelper->convertVar($object, $template->getMetaKeyword());
        } else {
            $metaKeyword = $this->dataHelper->convertCategoryVar($object, $template->getMetaKeyword());
        }
        $metaKeyword = chop($metaKeyword);

        $metaKeyword = preg_replace("/\r\n|\r|\n/", ' ', $metaKeyword);
        $metaKeyword = preg_replace('/[\s]+/mu', ' ', $metaKeyword);

        $storeId = $object->getStoreId();
        $maxMetaKeyword = $this->dataHelper->getConfig('bss_metatagmanager/' . $type . '/meta_keyword_length', $storeId);
        $metaKeyword = strip_tags($this->dataHelper->truncateString($metaKeyword, $maxMetaKeyword));

        $metaKeyword = trim($metaKeyword);
        $metaKeyword = rtrim($metaKeyword, ",");
        $metaKeyword = (string)$metaKeyword;
        return $metaKeyword;
    }

    /**
     * @param object $object
     * @param object $template
     * @param string $type
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function handleDescription($object, $template, $type = 'product') : string
    {
        if ($type === 'product') {
            $description = $this->dataHelper->convertVar(
                $object,
                $template->getFullDescription()
            );
        } else {
            $description = $this->dataHelper->convertCategoryVar(
                $object,
                $template->getFullDescription()
            );
        }
        $description = (string)$description;
        return $description;
    }

    /**
     * @param object $product
     * @param object $template
     * @param string $type
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function handleShortDescription($product, $template, $type = 'product') : string
    {
        if ($type === 'product') {
            $shortDescription = $this->dataHelper->convertVar(
                $product,
                $template->getShortDescription()
            );
        } else {
            $shortDescription = '';
        }
        $shortDescription = (string)$shortDescription;
        return $shortDescription;
    }

    /**
     * @param object $object
     * @param object $template
     * @param string $type
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function handleUrlKey($object, $template, $type = 'product') : string
    {
        if ($type === 'product') {
            $urlKey = $this->dataHelper->convertVar($object, $template->getUrlKey());
            $suffixEntity = $this->dataHelper->getProductSuffixUrl($object->getStoreId());
        } else {
            $urlKey = $this->dataHelper->convertCategoryVar($object, $template->getUrlKey());
            $suffixEntity = $this->dataHelper->getCategorySuffixUrl($object->getStoreId());
        }
        $urlKey = chop($urlKey);

        $urlKey = preg_replace("/\r\n|\r|\n/", ' ', $urlKey);
        $urlKey = preg_replace('/[\s]+/mu', ' ', $urlKey);
        $storeId = $object->getStoreId();
        if (!(int)$storeId) {
            $storeId = $this->dataHelper->getStoreManager()->getDefaultStoreView()->getStoreId();
        }
        $maxUrlKey = $this->dataHelper->getConfig('bss_metatagmanager/' . $type . '/url_key', $storeId);
        $urlKey = strip_tags($this->dataHelper->truncateString($urlKey, $maxUrlKey));

        $urlKey = trim($urlKey);
        $urlKey = rtrim($urlKey, ",");
        $urlKey = $this->dataHelper->createSlugByString($urlKey);
        return $this->processUrlKeyOld($object, $urlKey, $type, $storeId, $suffixEntity);
    }

    /**
     * @param object $object
     * @param string $urlKey
     * @param string $type
     * @param int $storeId
     * @param string $suffixEntity
     * @return string
     */
    public function processUrlKeyOld($object, $urlKey, $type, $storeId, $suffixEntity)
    {
        $requestPath = $urlKey . $suffixEntity;

        $paramData = [
            UrlRewrite::REQUEST_PATH => $requestPath,
            UrlRewrite::ENTITY_TYPE => $type
        ];
        if ((int)$storeId) {
            $paramData[UrlRewrite::STORE_ID] = $storeId;
        }

        //Check with Old URL key
        $oldUrlKey = $object->getUrlKey();
        if ($oldUrlKey && $urlKey && strpos((string)$oldUrlKey, (string)$urlKey) !== false) {
            return $oldUrlKey;
        }

        //Check With DB
        $rewrite = $this->urlFinder->findOneByData($paramData);
        if ($rewrite && (int)$rewrite->getEntityId() !== (int)$object->getId()) {
            $urlKey = $urlKey . $this->dataHelper->getRandomString(6);
            $requestPath = $urlKey . $suffixEntity;
            $paramData[UrlRewrite::REQUEST_PATH] = $requestPath;
            while ($this->urlFinder->findOneByData($paramData)) {
                $urlKey = $urlKey . $this->dataHelper->getRandomString(6);
                $requestPath = $urlKey . $suffixEntity;
                $paramData[UrlRewrite::REQUEST_PATH] = $requestPath;
            }
        }
        $urlKey = (string)$urlKey;
        return $urlKey;
    }
}
