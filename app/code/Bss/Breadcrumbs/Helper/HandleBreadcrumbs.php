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
 * @package    Bss_Breadcrumbs
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Breadcrumbs\Helper;

class HandleBreadcrumbs
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Bss\Breadcrumbs\Model\PathFactory
     */
    protected $pathFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * HandleBreadcrumbs constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\Breadcrumbs\Model\PathFactory $pathFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\Breadcrumbs\Model\PathFactory $pathFactory
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->pathFactory = $pathFactory;
    }

    /**
     * Get all category by product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllCategory($product)
    {
        $categoryArray = $product->getCategoryIds();

        if (empty($categoryArray)) {
            return null;
        }

        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

        $categoryIds = $this->checkFlatCategory($categoryArray, $rootCategoryId);

        $categories = $this->getCategoryCollection()
            ->addAttributeToFilter('entity_id', $categoryIds)->addIsActiveFilter();
        $maxs = null;
        $myCategory = null;
        foreach ($categories as $key => $category) {
            if ($category->getIsActive() && $this->checkEnableCategory($category)) {
                $maxs = $key;
            }
        }
        foreach ($categories as $key => $category) {
            if ($key == $maxs) {
                $myCategory = $category;
            }
        }
        return $myCategory;
    }

    /**
     * Check if category is enable or not
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return bool
     */
    public function checkEnableCategory($category)
    {
        $result = 2;
        $categoryLevel = (int)$category->getLevel();

        while ((int)$category->getLevel() >= 2) {
            $category = $category->getParentCategory();

            $parentCategoryLevel = (int)$category->getLevel();
            if ($parentCategoryLevel == 2) {
                if ($category->getIsActive()) {
                    $result++;
                }
                break;
            } else {
                if ($category->getIsActive()) {
                    $result++;
                }
            }
        }
        if ($result == $categoryLevel) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check for flat category
     *
     * @param array $categoryArray
     * @param int $rootCategoryId
     * @return array
     */
    public function checkFlatCategory($categoryArray, $rootCategoryId)
    {
        $categoryReturn = [];
        $rootCategoryId = '/' . $rootCategoryId . '/';

        foreach ($categoryArray as $categoryId) {
            $pathCategory = $this->pathFactory->create()->getCollection()->addFieldToFilter('entity_id', $categoryId);
            $path = '/' . $pathCategory->getData()[0]['path'] . '/';

            if (strpos((string)$path, (string)$rootCategoryId) === false) {
            } else {
                $categoryReturn[] = $categoryId;
            }
        }
        return $categoryReturn;
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
}