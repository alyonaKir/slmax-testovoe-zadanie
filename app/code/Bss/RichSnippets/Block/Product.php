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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Block;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Product
 * @package Bss\RichSnippets\Block
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\RichSnippets\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var $collectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var ObjectManagerInterface
     */
    protected $myObjectManager;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewsColFactory;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $pageTitle;
    
    /**
     * Review collection
     *
     * @var ReviewCollection
     */
    protected $reviewsCollection;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productLoader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * Product constructor.
     * @param \Bss\RichSnippets\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Page\Title $pageTitle
     * @param \Magento\Framework\App\Request\Http $request
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Catalog\Model\ProductFactory $productLoader
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\RichSnippets\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\Framework\App\Request\Http $request,
        ObjectManagerInterface $objectManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \Magento\Framework\View\Page\Config $layoutFactory,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->productLoader = $productLoader;
        $this->storeManagerInterface = $context->getStoreManager();
        $this->request = $request;
        $this->pageTitle = $pageTitle;
        $this->layoutFactory = $layoutFactory;
        $this->storeManager = $context->getStoreManager();
        $this->reviewsColFactory = $collectionFactory;
        $this->myObjectManager = $objectManager;
        $this->reviewFactory = $reviewFactory;
        $this->registry = $registry;
        $this->urlInterface = $context->getUrlBuilder();
        $this->scopeConfig = $context->getScopeConfig();
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Bss\RichSnippets\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return mixed
     */
    public function getCurrentUrl()
    {
        return $this->storeManagerInterface->getStore()->getCurrentUrl();
    }

    /**
     * @return mixed
     */
    public function getMediaUrl()
    {
        $mediaDir = $this->storeManagerInterface
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaDir;
    }

    /**
     * @param string $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($product)
    {
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        return $stockItem;
    }

    /**
     * @param string $productId
     * @return mixed
     */
    public function getRatingSummary($productId)
    {
        $product = $this->productLoader->create()->load($productId);
        $this->reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());
        $arrRating['count'] = $product->getRatingSummary()->getReviewsCount();
        $arrRating['value'] = $product->getRatingSummary()->getRatingSummary();
        return $arrRating;
    }

    /**
     * @param string $productId
     * @return $this|ReviewCollection
     */
    public function getReviewsCollection($productId)
    {
        if (null === $this->reviewsCollection) {
            $this->reviewsCollection = $this->reviewsColFactory->create()->addStoreFilter(
                $this->storeManager->getStore()->getId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $productId
            )->setDateOrder();
        }
        return $this->reviewsCollection;
    }

    /**
     * Return base url.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param   string $query
     * @return  string
     */
    public function getResultUrl()
    {
        return $this->urlInterface->getUrl(
            'catalogsearch/result'
        );
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get current product 
     * @return Product
     */
    public function getCurrentProduct()
    {
        $currentProduct = $this->registry->registry('current_product');
        return $currentProduct;
    }

    /**
     * @return mixed
     */
    public function getTitlePage()
    {
        return $this->pageTitle->getShort();
    }

    /**
     * @return mixed
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }

    /**
     * @return string
     */
    public function getTypePage()
    {
        return $this->request->getFullActionName();
    }


    /**
     * @return mixed
     */
    public function getCurrentCategoryOb()
    {
        return $this->coreRegistry->registry('current_category');
    }
}
