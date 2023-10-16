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

namespace Bss\Breadcrumbs\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Breadcrumbs
 *
 * @package Bss\Breadcrumbs\Block
 */
class Breadcrumbs extends \Magento\Catalog\Block\Breadcrumbs
{
    /**
     * @var \Bss\Breadcrumbs\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    protected $_catalogData;

    /**
     * @var \Bss\Breadcrumbs\Model\PathFactory
     */
    protected $pathFactory;

    /**
     * @var \Bss\Breadcrumbs\Helper\HandleBreadcrumbs
     */
    protected $handleBreadcrumbs;

    /**
     * Breadcrumbs constructor.
     * @param Context $context
     * @param \Bss\Breadcrumbs\Helper\Data $dataHelper
     * @param Data $catalogData
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Bss\Breadcrumbs\Model\PathFactory $pathFactory
     * @param \Bss\Breadcrumbs\Helper\HandleBreadcrumbs $handleBreadcrumbs
     * @param array $data
     */
    public function __construct(
        Context                                   $context,
        \Bss\Breadcrumbs\Helper\Data              $dataHelper,
        Data                                      $catalogData,
        \Magento\Framework\App\Request\Http       $request,
        \Magento\Framework\Registry               $registry,
        CategoryRepositoryInterface               $categoryRepository,
        \Bss\Breadcrumbs\Model\PathFactory        $pathFactory,
        \Bss\Breadcrumbs\Helper\HandleBreadcrumbs $handleBreadcrumbs,
        array                                     $data = []
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->request = $request;
        $this->coreRegistry = $registry;
        $this->dataHelper = $dataHelper;
        $this->_catalogData = $catalogData;
        $this->pathFactory = $pathFactory;
        $this->handleBreadcrumbs = $handleBreadcrumbs;
        parent::__construct($context, $catalogData, $data);
    }

    /**
     * Get StoreId
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @inheritDoc
     *
     * @return $this|\Magento\Catalog\Block\Breadcrumbs
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        $storeId = $this->getStoreId();
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $title = [];
            $breadcrumbsEnable = $this->dataHelper->getBreadcrumbsEnable($storeId);

            $pathCate = null;

            $path = null;
            if ($breadcrumbsEnable == '1') {
                $typePage = $this->getTypePage();
                if ($typePage == 'catalog_category_view') {
                    $pathType = $this->dataHelper->getBreadcrumbsType($storeId);
                    $pathCate = $this->getCategoryById($this->getCurrentCategoryOb()->getId())->getData('path');
                    $priorityCate = $this->getPriorityCate();

                    if ($pathType == 'short') {
                        $path = $this->pathSortArray($pathCate, $priorityCate);
                    } else {
                        $path = $this->pathLongArray($pathCate, $priorityCate);
                    }
                }

                if ($typePage == 'catalog_product_view') {
                    $product = $this->getCurrentProductOb();
                    $categoryAll = $this->handleBreadcrumbs->getAllCategory($product);
                    $pathType = $this->dataHelper->getBreadcrumbsType($storeId);
                    if (null !== $categoryAll) {
                        $pathCate = $this->getCategoryById($categoryAll->getId())->getData('path');
                    }
                    $priorityCate = $this->getPriorityCateProduct($product);

                    $productArray['label'] = $product->getName();
                    $productArray['link'] = $product->getProductUrl();

                    if ($pathType == 'short') {
                        $path = $this->pathSortProductArray($pathCate, $priorityCate, $productArray);
                    } else {
                        $path = $this->pathLongProductArray($pathCate, $priorityCate, $productArray);
                    }
                }
            } else {
                $path = $this->_catalogData->getBreadcrumbPath();
            }
            foreach ($path as $name => $breadcrumb) {
                $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                $title[] = $breadcrumb['label'];
            }


            $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($title)));
        }
        return $this;
    }

    /**
     * Get CrumbsProduct for path store
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCrumbsProduct()
    {
        $storeId = $this->getStoreId();

        $breadcrumbsEnable = $this->dataHelper->getBreadcrumbsEnable($storeId);

        $pathCate = null;

        $path = null;
        if ($breadcrumbsEnable == '1') {
            $typePage = $this->getTypePage();
            if ($typePage == 'catalog_category_view') {
                $pathType = $this->dataHelper->getBreadcrumbsType($storeId);
                $pathCate = $this->getCategoryById($this->getCurrentCategoryOb()->getId())->getData('path');
                $priorityCate = $this->getPriorityCate();

                if ($pathType == 'short') {
                    $path = $this->pathSortArray($pathCate, $priorityCate);
                } else {
                    $path = $this->pathLongArray($pathCate, $priorityCate);
                }
            }

            if ($typePage == 'catalog_product_view') {
                $product = $this->getCurrentProductOb();
                $categoryAll = $this->handleBreadcrumbs->getAllCategory($product);
                $pathType = $this->dataHelper->getBreadcrumbsType($storeId);
                if (null !== $categoryAll) {
                    $pathCate = $this->getCategoryById($categoryAll->getId())->getData('path');
                }
                $priorityCate = $this->getPriorityCateProduct($product);

                $productArray['label'] = $product->getName();
                $productArray['link'] = $product->getProductUrl();

                if ($pathType == 'short') {
                    $path = $this->pathSortProductArray($pathCate, $priorityCate, $productArray);
                } else {
                    $path = $this->pathLongProductArray($pathCate, $priorityCate, $productArray);
                }
            }
        } else {
            $path = $this->_catalogData->getBreadcrumbPath();
        }
        return $this->prepareBreadcrumbsArray($path);
    }

    /**
     * Prepare Breadcrumbs
     *
     * @param array $path
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareBreadcrumbsArray($path)
    {
        $breadcrumbsBlock = [];
        $breadcrumbsBlock['home'] = [
            'last' => false,
            'label' => __('Home'),
            'title' => __('Go to Home Page'),
            'link' => $this->_storeManager->getStore()->getBaseUrl()
        ];
        foreach ($path as $name => $breadcrumb) {
            $breadcrumb['title'] = $breadcrumb['label'];
            if ($name == 'product') {
                $breadcrumb['last'] = true;
            } else {
                $breadcrumb['last'] = false;
            }
            if (!isset($breadcrumb['link'])) {
                $breadcrumb['link'] = false;
            }
            $breadcrumbsBlock[$name] = $breadcrumb;
        }

        return $breadcrumbsBlock;
    }

    /**
     * Get category priority
     *
     * @return \Magento\Catalog\Model\Category|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPriorityCate()
    {
        $storeId = $this->getStoreId();
        $usePriorityCate = $this->dataHelper->getBreadcrumbsPriority($storeId);
        if ($usePriorityCate == '1') {
            $priorityCate = $this->getCategoryById($this->getCurrentCategoryOb()->getId())->getData('priority_id');
        } else {
            $priorityCate = null;
        }
        return $priorityCate;
    }

    /**
     * Get category priority by product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPriorityCateProduct($product)
    {
        $storeId = $this->getStoreId();
        $usePriorityCate = $this->dataHelper->getBreadcrumbsPriority($storeId);
        $allCategory = $this->handleBreadcrumbs->getAllCategory($product);
        if ($usePriorityCate == '1') {
            if (null !== $allCategory) {
                $priorityCate = $allCategory->getData('priority_id');
            } else {
                $priorityCate = null;
            }
        } else {
            $priorityCate = null;
        }
        return $priorityCate;
    }

    /**
     * Passing to array
     *
     * @param string $pathCate
     * @param string $priorityCate
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function pathLongArray($pathCate, $priorityCate)
    {
        $result = [];
        if ($pathCate != null & $pathCate != '') {
            $pathCate = explode('/', $pathCate);
            if ($priorityCate == null || $priorityCate == '') {
                $priorityCate = end($pathCate);
            } else {
                $levelPriority = (int)$this->getCategoryById($priorityCate)->getData('level');

                if ($levelPriority < 2) {
                    $priorityCate = end($pathCate);
                }
            }

            $result = $this->checkPathLongArray($pathCate, $priorityCate);
        }
        return $result;
    }

    /**
     * Check for long path array
     *
     * @param array $pathCate
     * @param string $priorityCate
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function checkPathLongArray($pathCate, $priorityCate)
    {
        $result = [];
        foreach ($pathCate as $value) {
            $levelCate = $this->getCategoryById($value)->getData('level');
            $levelCate = (int)$levelCate;

            $resultName = 'category' . $value;
            $valueNumber = (int)$value;
            $priorityCateNumber = (int)$priorityCate;

            if ($levelCate > 1 && $valueNumber <= $priorityCateNumber) {
                $result[$resultName]['label'] = $this->getCategoryById($value)->getName();
                if ($value != end($pathCate) && $valueNumber != $priorityCateNumber) {
                    $result[$resultName]['link'] = $this->getCategoryById($value)->getUrl();
                } else {
                    $result[$resultName]['link'] = null;
                }
            }
        }

        return $result;
    }

    /**
     * Path sorting
     *
     * @param array $pathCate
     * @param string $priorityCate
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function pathSortArray($pathCate, $priorityCate)
    {
        $result = [];
        if ($pathCate != null & $pathCate != '') {
            $pathCate = explode('/', $pathCate);
            if ($priorityCate == null || $priorityCate == '') {
                $priorityCate = end($pathCate);
            } else {
                $levelPriority = (int)$this->getCategoryById($priorityCate)->getData('level');
                if ($levelPriority < 2) {
                    $priorityCate = end($pathCate);
                }
            }
            foreach ($pathCate as $value) {
                $levelCate = $this->getCategoryById($value)->getData('level');
                $levelCate = (int)$levelCate;
                $valueNumber = (int)$value;
                $priorityCateNumber = (int)$priorityCate;
                if ($levelCate > 1 && $valueNumber <= $priorityCateNumber) {
                    if ($value == $priorityCate) {
                        $resultName = 'category' . $value;
                        $result[$resultName]['label'] = $this->getCategoryById($value)->getName();
                        $result[$resultName]['link'] = null;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Long path for product
     *
     * @param string $pathCate
     * @param string $priorityCate
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function pathLongProductArray($pathCate, $priorityCate, $product = null)
    {
        $result = [];
        if ($pathCate != null & $pathCate != '') {
            $pathCate = explode('/', $pathCate);
            if ($priorityCate == null || $priorityCate == '') {
                $priorityCate = end($pathCate);
            } else {
                $levelPriority = (int)$this->getCategoryById($priorityCate)->getData('level');
                if ($levelPriority < 2) {
                    $priorityCate = end($pathCate);
                }
            }
            foreach ($pathCate as $value) {
                $levelCate = $this->getCategoryById($value)->getData('level');
                $levelCate = (int)$levelCate;
                if ($levelCate > 1) {
                    $resultName = 'category' . $value;
                    $valueNumber = (int)$value;
                    $priorityCateNumber = (int)$priorityCate;
                    if ($valueNumber <= $priorityCateNumber) {
                        $result[$resultName]['label'] = $this->getCategoryById($value)->getName();
                        $result[$resultName]['link'] = $this->getCategoryById($value)->getUrl();
                    }
                }
            }
            $result['product']['label'] = $product['label'];
        } else {
            $result['product']['label'] = $product['label'];
        }
        return $result;
    }

    /**
     * Sort path for product
     *
     * @param string $pathCate
     * @param string $priorityCate
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function pathSortProductArray($pathCate, $priorityCate, $product = null)
    {
        $result = [];
        if ($pathCate != null & $pathCate != '') {
            $pathCate = explode('/', $pathCate);
            if ($priorityCate == null || $priorityCate == '') {
                $priorityCate = end($pathCate);
            } else {
                $levelPriority = (int)$this->getCategoryById($priorityCate)->getData('level');
                if ($levelPriority < 2) {
                    $priorityCate = end($pathCate);
                }
            }
            foreach ($pathCate as $value) {
                $levelCate = $this->getCategoryById($value)->getData('level');
                $levelCate = (int)$levelCate;
                $valueNumber = (int)$value;
                $priorityCateNumber = (int)$priorityCate;
                if ($levelCate > 1 && $valueNumber <= $priorityCateNumber) {
                    if ($value == $priorityCate) {
                        $resultName = 'category' . $value;
                        $result[$resultName]['label'] = $this->getCategoryById($value)->getName();
                        $result[$resultName]['link'] = $this->getCategoryById($value)->getUrl();
                    }
                }
            }
            $result['product']['label'] = $product['label'];
        } else {
            $result['product']['label'] = $product['label'];
        }
        return $result;
    }

    /**
     * Get the page action name
     *
     * @return string
     */
    public function getTypePage()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Get category by ID
     *
     * @param int $categoryId
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryById($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);
        return $category;
    }

    /**
     * Get current category
     *
     * @return mixed
     */
    public function getCurrentCategoryOb()
    {
        return $this->coreRegistry->registry('current_category');
    }


    /**
     * Get current product
     *
     * @return mixed
     */
    public function getCurrentProductOb()
    {
        $currentProduct = $this->coreRegistry->registry('current_product');
        return $currentProduct;
    }

}

