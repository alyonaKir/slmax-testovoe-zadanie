<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bunsdled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CanonicalTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CanonicalTag\Block;

/**
 * Class CanonicalTag
 *
 * @package Bss\CanonicalTag\Block
 */
class CanonicalTag extends \Magento\Framework\View\Element\Template
{
    /**
     * @var $collectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    public $productUrlPathGenerator;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * @var \Bss\CanonicalTag\Helper\ProductData
     */
    public $bssHelperData;

    /**
     * @var \Bss\CanonicalTag\Helper\Data
     */
    public $dataHelper;

    /**
     * CanonicalTag constructor.
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param \Bss\CanonicalTag\Helper\ProductData $bssHelperData
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Bss\CanonicalTag\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        \Bss\CanonicalTag\Helper\ProductData $bssHelperData,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Bss\CanonicalTag\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->bssHelperData = $bssHelperData;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Get store ID
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get store by ID
     *
     * @param int $id
     * @return mixed
     */
    public function getStoreById($id)
    {
        return $this->groupRepository->get($id);
    }

    /**
     * Get current CMS
     *
     * @return \Magento\Cms\Model\Page
     */
    public function getCurrentCms()
    {
        return $this->bssHelperData->getCurrentCms();
    }

    /**
     * Get data helper
     *
     * @return \Bss\CanonicalTag\Helper\Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
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
     * Get current HrefLang
     *
     * @param int $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrlHreflang($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getCurrentUrl(false);
    }

    /**
     * Get Helper
     *
     * @return \Bss\CanonicalTag\Helper\ProductData
     */
    public function getHelper()
    {
        return $this->bssHelperData;
    }

    /**
     * Get current Url
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrl()
    {
        return $this->_storeManager->getStore()->getCurrentUrl();
    }

    /**
     * Check if type is category
     *
     * @param string $type
     * @return bool
     */
    public function isCategory($type)
    {
        if ($type == 'cms') {
            if (strpos($this->request->getFullActionName(), 'cms') === false) {
                return false;
            } else {
                if ($this->request->getFullActionName() == 'cms_index_index') {
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            if ($this->request->getFullActionName() == $type) {
                return true;
            }
            return false;
        }
    }

    /**
     * Check if type is Cms key
     *
     * @param string $cmsKey
     * @return bool
     */
    public function checkTypeCms($cmsKey)
    {
        if (strpos($this->request->getFullActionName(), 'cms') === false) {
            return false;
        } else {
            $cmsUrlKey = $this->getCurrentCms()->getIdentifier();
            if ($cmsKey === $cmsUrlKey) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Get base Url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get store code
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get current product
     *
     * @return mixed
     */
    public function getCurrentProduct()
    {
        $currentProduct = $this->bssHelperData->getRegistry()->registry('current_product');
        return $currentProduct;
    }

    /**
     * Get current category
     *
     * @return mixed
     */
    public function getCurrentCategory()
    {
        $currentCategory = $this->bssHelperData->getRegistry()->registry('current_category');
        return $currentCategory;
    }

    /**
     * Get page type
     *
     * @return string
     */
    public function getTypePage()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Get observer
     *
     * @return mixed
     */
    public function getCurrentCategoryOb()
    {
        return $this->bssHelperData->getRegistry()->registry('current_category');
    }

    /**
     * Get all category
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Category |null
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
