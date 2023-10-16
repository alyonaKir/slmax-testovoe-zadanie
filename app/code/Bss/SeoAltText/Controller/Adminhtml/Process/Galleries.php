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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Controller\Adminhtml\Process;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

/**
 * Class Galleries
 * @package Bss\SeoAltText\Controller\Adminhtml\Process
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Galleries extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var GalleryReadHandler
     */
    private $galleryReadHandler;
    /**
     * @var Context
     */
    protected $context;

    /**
     * Galleries constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GalleryReadHandler $galleryReadHandler
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GalleryReadHandler $galleryReadHandler,
        Context $context
    ) {
        $this->galleryReadHandler = $galleryReadHandler;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->context = $context;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $dataReturn = [
            "status" => false,
            "data" => [],
            "error_type" => ""
        ];
        if ($this->getRequest()->isAjax()) {
            $dataReturn['status'] = true;
            $page = $this->getRequest()->getPost('page');
            $perPage = $this->getRequest()->getPost('per_page');
            $filtersData = $this->getRequest()->getPost('filters');
            if (!$page) {
                $page = 1;
            }
            if (!$perPage) {
                $perPage = \Bss\SeoAltText\Helper\Data::SEO_TOOLBAR_ALBUM_PER_PAGE;
            }
            $dataGalleries = $this->getAllGalleries($page, $perPage, $filtersData);
            if (!empty($dataGalleries)) {
                $dataReturn['status'] = true;
                $dataReturn['data'] = $dataGalleries;
            }
        }

        $result->setData($dataReturn);
        return $result;
    }

    /**
     * @param string $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl($storeId)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param string $imageFile
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl($imageFile)
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        $imageUrl = $mediaUrl . $imageFile;
        return $imageUrl;
    }

    /**
     * @param string $imageFile
     * @return mixed|string
     */
    public function getImageFile($imageFile)
    {
        //Check End Path
        $finalPath = '';
        $imageFileArray = explode('/', $imageFile);
        if (!empty($imageFileArray)) {
            $finalPath = end($imageFileArray);
        }
        return $finalPath;
    }

    /**
     * @param string $page
     * @param string $perPage
     * @param array $dataFilters
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllGalleries($page, $perPage, $dataFilters)
    {
        $productCollection = $this->getProductCollection($page, $perPage, $dataFilters);
        $imageObject = [];
        if ($productCollection->getSize()) {
            foreach ($productCollection as $product) {
                $this->addGallery($product);
                $mediaGallery = $product->getData('media_gallery');
                $imagesToAdd = [];
                foreach ($mediaGallery['images'] as $imagesCode) {
                    $imagesUrl = [
                        'url' => $this->getImageUrl($imagesCode['file']),
                        'alt' => $imagesCode['label'],
                        'value_id' => $imagesCode['value_id'],
                        'position' => $imagesCode['position'],
                        'file' => $this->getImageFile($imagesCode['file'])
                    ];
                    $imagesToAdd[] = $imagesUrl;
                }
                $dataToAdd = [
                    'images' => $imagesToAdd,
                    'product_name' => $product->getName(),
                    'product_sku' => $product->getSku(),
                    'product_id' => $product->getId(),
                    'edit_url' => $this->getUrlProduct($product->getId(), $product->getStoreId())
                ];
                $imageObject[] = $dataToAdd;
            }
        }
        return $imageObject;
    }

    /**
     * @param string $productId
     * @param string $storeId
     * @return string
     */
    public function getUrlProduct($productId, $storeId)
    {
        $param = ['store' => $storeId];
        $backendUrl = $this->context->getBackendUrl()->getUrl('catalog/product/edit/id/' . $productId, $param);
        return $backendUrl;
    }

    /**
     * @param string $page
     * @param string $perPage
     * @param array $dataFilters
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($page, $perPage, $dataFilters)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection = $this->processFiltersCollection($collection, $dataFilters);
        $collection->setPageSize($perPage);
        $collection->setCurPage($page);
        return $collection;
    }

    /**
     * @param object $collection
     * @param array $dataFilters
     * @return mixed
     */
    public function processFiltersCollection($collection, $dataFilters)
    {
        if (isset($dataFilters['status']) && $dataFilters['status'] !== '') {
            $collection->addAttributeToFilter('status', $dataFilters['status']);
        }
        if (isset($dataFilters['visibility']) && $dataFilters['visibility'] !== '') {
            $collection->addAttributeToFilter('visibility', $dataFilters['visibility']);
        }
        if (isset($dataFilters['name']) && $dataFilters['name'] !== '') {
            $collection->addAttributeToFilter('name', ['like' => '%' . $dataFilters['name'] . '%']);
        }
        $collection = $this->processFiltersCollectionMore($collection, $dataFilters);
        return $collection;
    }

    /**
     * @param object $collection
     * @param array $dataFilters
     * @return mixed
     */
    public function processFiltersCollectionMore($collection, $dataFilters)
    {
        if (isset($dataFilters['sku']) && $dataFilters['sku'] !== '') {
            $collection->addAttributeToFilter('sku', ['like' => '%' . $dataFilters['sku'] . '%']);
        }
        if (isset($dataFilters['attributeSet']) && $dataFilters['attributeSet'] !== '') {
            $collection->addFieldToFilter('attribute_set_id', $dataFilters['attributeSet']);
        }
        if (isset($dataFilters['type']) && $dataFilters['type'] !== '') {
            $collection->addFieldToFilter('type_id', $dataFilters['type']);
        }
        if (isset($dataFilters['store']) && $dataFilters['store'] !== '') {
            $collection->addStoreFilter($dataFilters['store']);
            $collection->setStoreId((int)$dataFilters['store']);
        }
        return $collection;
    }

    /**
     * @param object $product
     */
    public function addGallery($product)
    {
        $this->galleryReadHandler->execute($product);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_SeoAltText::seo_alt_text');
    }
}
