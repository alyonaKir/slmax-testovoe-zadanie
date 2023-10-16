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
namespace Bss\Redirects301Seo\Block;

/**
 * Class Redirect301Seo
 *
 * @package Bss\Redirects301Seo\Block
 */
class Redirect301Seo extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    public $productUrlPathGenerator;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * Redirect301Seo constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productRepository = $productRepository;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        parent::__construct($context, $data);
    }

    /**
     * Get canonical Url path
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param object $category
     * @return string
     */
    public function getCanonicalUrlPath($product, $category = null)
    {
        return $this->productUrlPathGenerator->getUrlPath($product, $category);
    }

    /**
     * @param string $productId
     * @return bool|object
     */
    public function getAllCategory($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);

            $categoryIds = $product->getCategoryIds();

            $categories = $this->getCategoryCollection()
                ->addAttributeToFilter('entity_id', $categoryIds);
            $maxKey = null;
            $finalCategory = false;
            foreach ($categories as $key => $category) {
                $maxKey = $key;
            }
            foreach ($categories as $key => $category) {
                if ($key === $maxKey) {
                    $finalCategory = $category;
                }
            }
            return $finalCategory;
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
            return false;
        }
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
