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

use Exception;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Class ProcessSaveEntity
 * @package Bss\MetaTagManager\Helper
 */
class ProcessSaveEntity
{
    /**
     * @var ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    private $categoryResource;
    /**
     * @var array
     */
    private $dataUrlRewriteClassNames;
    /**
     * @var UrlRewriteBunchReplacer
     */
    private $urlRewriteBunchReplacer;
    /**
     * @var CategoryUrlRewriteGenerator
     */
    private $categoryUrlRewriteGenerator;
    /**
     * @var DatabaseMapPool
     */
    private $databaseMapPool;
    /**
     * @var ProductUrlRewriteGenerator
     */
    private $productUrlRewriteGenerator;
    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * ProcessSaveEntity constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlRewriteBunchReplacer $urlRewriteBunchReplacer
     * @param DatabaseMapPool $databaseMapPool
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param array $dataUrlRewriteClassNames
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlRewriteBunchReplacer $urlRewriteBunchReplacer,
        DatabaseMapPool $databaseMapPool,
        ProductUrlPathGenerator $productUrlPathGenerator,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        $dataUrlRewriteClassNames = [
            DataCategoryUrlRewriteDatabaseMap::class,
            DataProductUrlRewriteDatabaseMap::class
        ]
    ) {
        $this->urlPersist = $urlPersist;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->databaseMapPool = $databaseMapPool;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlRewriteBunchReplacer = $urlRewriteBunchReplacer;
        $this->dataUrlRewriteClassNames = $dataUrlRewriteClassNames;
        $this->categoryResource = $categoryResource;
        $this->productResource = $productResource;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
    }

    /**
     * @param object $category
     * @param array $metaData
     * @return $this
     * @throws Exception
     */
    public function handleCategoryMeta($category, $metaData)
    {
        if ($metaData['meta_description']) {
            $category->setData('meta_description', $metaData['meta_description']);
            $this->categoryResource->saveAttribute($category, 'meta_description');
        }
        if ($metaData['description']) {
            //$description = $this->filterProvider->getPageFilter()->filter($description);
            $category->setData('description', $metaData['description']);
            $this->categoryResource->saveAttribute($category, 'description');
        }

        if ($metaData['meta_title']) {
            $category->setData('meta_title', $metaData['meta_title']);
            $this->categoryResource->saveAttribute($category, 'meta_title');
        }
        $this->handleCategoryUrlKey($category, $metaData);
        return $this;
    }

    /**
     * @param object $category
     * @param object $metaData
     * @throws Exception
     */
    public function handleCategoryUrlKey($category, $metaData)
    {
        if ($metaData['meta_keyword']) {
            $category->setData('meta_keywords', $metaData['meta_keyword']);
            $this->categoryResource->saveAttribute($category, 'meta_keywords');
        }
        $oldUrlKey = $category->getUrlKey();
        if ($metaData['url_key'] && $metaData['url_key'] !== $oldUrlKey) {
            $category->setData('url_key', $metaData['url_key']);
            $this->categoryResource->saveAttribute($category, 'url_key');
            $categoryUrlRewriteResult = $this->categoryUrlRewriteGenerator->generate($category);
            $this->urlRewriteBunchReplacer->doBunchReplace($categoryUrlRewriteResult);
            $this->resetUrlRewritesDataMaps($category);
        }
    }

    /**
     * @param object $product
     * @param array $metaData
     * @return bool
     * @throws Exception
     */
    public function handleProductMeta($product, $metaData)
    {
        if ($metaData['short_description']) {
            $product->setData('short_description', $metaData['short_description']);
            $this->productResource->saveAttribute($product, 'short_description');
        }

        if ($metaData['description']) {
            $product->setData('description', $metaData['description']);
            $this->productResource->saveAttribute($product, 'description');
        }

        if ($metaData['meta_title']) {
            $product->setData('meta_title', $metaData['meta_title']);
            $this->productResource->saveAttribute($product, 'meta_title');
        }

        if ($metaData['meta_description']) {
            $product->setData('meta_description', $metaData['meta_description']);
            $this->productResource->saveAttribute($product, 'meta_description');
        }
        $this->handleProductUrlKey($product, $metaData);
        return true;
    }

    /**
     * @param object $product
     * @param array $metaData
     * @return bool
     * @throws Exception
     */
    public function handleProductUrlKey($product, $metaData)
    {
        if ($metaData['meta_keyword']) {
            $product->setData('meta_keyword', $metaData['meta_keyword']);
            $this->productResource->saveAttribute($product, 'meta_keyword');
        }
        $oldUrlKey = $product->getUrlKey();
        if ($metaData['url_key'] && $metaData['url_key'] !== $oldUrlKey) {
            $product->setData('url_key', $metaData['url_key']);
            $this->productResource->saveAttribute($product, 'url_key');
            $product->setUrlPath($this->productUrlPathGenerator->getUrlPath($product));
            $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));
        }
        return true;
    }

    /**
     * Resets used data maps to free up memory and temporary tables
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     */
    protected function resetUrlRewritesDataMaps($category)
    {
        foreach ($this->dataUrlRewriteClassNames as $className) {
            $this->databaseMapPool->resetMap($className, $category->getEntityId());
        }
    }
}
