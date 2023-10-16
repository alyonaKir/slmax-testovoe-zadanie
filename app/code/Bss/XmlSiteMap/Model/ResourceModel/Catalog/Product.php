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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Model\ResourceModel\Catalog;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class Product
 *
 * @package Bss\XmlSiteMap\Model\ResourceModel\Catalog
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const NOT_SELECTED_IMAGE = 'no_selection';

    /**
     * Collection Zend Db select
     *
     * @var \Magento\Framework\DB\Select
     */
    public $select;

    /**
     * Attribute cache
     *
     * @var array
     */
    public $attributesCache = [];

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ReadHandler
     */
    public $mediaGalleryReadHandler;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    public $productResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    public $productVisibility;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    public $productStatus;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    public $mediaGalleryResourceModel;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    public $mediaConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var \Bss\XmlSiteMap\Helper\Data
     */
    public $sitemapData;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $productCollectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    public $dataObjectFactory;

    /**
     * @var \Magento\Framework\DataObject
     */
    public $dataObject;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResource;
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * Product constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Bss\XmlSiteMap\Helper\Data $sitemapData
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $mediaGalleryResourceModel
     * @param \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryReadHandler
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\DataObject $dataObject
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param UrlFinderInterface $urlFinder
     * @param null $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Bss\XmlSiteMap\Helper\Data $sitemapData,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $mediaGalleryResourceModel,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryReadHandler,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        UrlFinderInterface $urlFinder,
        $connectionName = null
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->productCollectionFactory = $objectManager;
        $this->dataObject = $dataObject;
        $this->productResource = $productResource;
        $this->storeManager = $storeManager;
        $this->productVisibility = $productVisibility;
        $this->productStatus = $productStatus;
        $this->mediaGalleryResourceModel = $mediaGalleryResourceModel;
        $this->mediaGalleryReadHandler = $mediaGalleryReadHandler;
        $this->mediaConfig = $mediaConfig;
        $this->sitemapData = $sitemapData;
        $this->date = $date;
        $this->urlFinder = $urlFinder;
        $this->categoryResource = $categoryResource;
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Add filter by store
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param string $value
     * @param string $type
     * @return bool|\Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addFilter($storeId, $attributeCode, $value, $type = '=')
    {
        if (!$this->select instanceof \Magento\Framework\DB\Select) {
            return false;
        }

        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            default:
                return false;
                break;
        }

        $attribute = $this->getAttribute($attributeCode);
        if ($attribute['backend_type'] == 'static') {
            $this->select->where('e.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->joinAttribute($storeId, $attributeCode);
            if ($attribute['is_global']) {
                $this->select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $this->getConnection()->getCheckSql(
                    't2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value',
                    't1_' . $attributeCode . '.value'
                );
                $this->select->where('(' . $ifCase . ')' . $conditionRule, $value);
            }
        }

        return $this->select;
    }

    /**
     * @param int $storeId
     * @param string $attributeCode
     * @param null $column
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function joinAttribute($storeId, $attributeCode, $column = null)
    {
        $connection = $this->getConnection();
        $attribute = $this->getAttribute($attributeCode);
        $linkField = $this->productResource->getLinkField();
        $attrTableAlias = 't1_' . $attributeCode;
        $this->select->joinLeft(
            [$attrTableAlias => $attribute['table']],
            "e.{$linkField} = {$attrTableAlias}.{$linkField}"
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.store_id = ?', Store::DEFAULT_STORE_ID)
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.attribute_id = ?', $attribute['attribute_id']),
            []
        );
        // Global scope attribute value
        $columnValue = 't1_' . $attributeCode . '.value';

        if (!$attribute['is_global']) {
            $attrTableAlias2 = 't2_' . $attributeCode;
            $this->select->joinLeft(
                ['t2_' . $attributeCode => $attribute['table']],
                "{$attrTableAlias}.{$linkField} = {$attrTableAlias2}.{$linkField}"
                . ' AND ' . $attrTableAlias . '.attribute_id = ' . $attrTableAlias2 . '.attribute_id'
                . ' AND ' . $connection->quoteInto($attrTableAlias2 . '.store_id = ?', $storeId),
                []
            );
            // Store scope attribute value
            $columnValue = $this->getConnection()->getIfNullSql('t2_' . $attributeCode . '.value', $columnValue);
        }

        // Add attribute value to result set if needed
        if (isset($column)) {
            $this->select->columns([
                $column => $columnValue
            ]);
        }
    }

    /**
     * Get Attribute
     *
     * @param int $attributeCode
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttribute($attributeCode)
    {
        if (!isset($this->attributesCache[$attributeCode])) {
            $attribute = $this->productResource->getAttribute($attributeCode);

            $this->attributesCache[$attributeCode] = [
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal() ==
                    \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend_type' => $attribute->getBackendType(),
            ];
        }
        return $this->attributesCache[$attributeCode];
    }

    /**
     * Filter product by store ud
     *
     * @param int $storeId
     * @return bool|\Zend_Db_Statement_Interface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function productFilter($storeId)
    {
        /* @var $store Store */
        $store = $this->storeManager->getStore($storeId);
        if (!$store) {
            return false;
        }

        $connection = $this->getConnection();
        $linkedFieldIdCategory = $this->categoryResource->getLinkField();
        $this->select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            [
                $this->getIdFieldName(),
                $this->productResource->getLinkField(),
                'updated_at',
                'type_id',
                'attribute_set_id'
            ]
        )->joinInner(
            ['w' => $this->getTable('catalog_product_website')],
            'e.entity_id = w.product_id',
            []
        )->joinLeft(
            ['url_rewrite' => $this->getTable('url_rewrite')],
            'e.entity_id = url_rewrite.entity_id AND url_rewrite.is_autogenerated = 1 '
            . 'AND NULLIF(url_rewrite.metadata,"") IS NULL'
            . $connection->quoteInto(' AND url_rewrite.store_id = ?', $storeId)
            . $connection->quoteInto(' AND url_rewrite.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE),
            ['url' => 'request_path']
        )->joinLeft(
            ['url_path' => $this->getTable('url_rewrite')],
            'e.entity_id = url_path.entity_id'
            . $connection->quoteInto(' AND url_path.store_id = ?', $store->getId()),
            ['url_path' => 'request_path']
        )->joinLeft(
            ['attribute_set' => $this->getTable('catalog_category_entity_varchar')],
            'e.attribute_set_id = attribute_set.' . $linkedFieldIdCategory . ' AND attribute_set.attribute_id = 124',
            ['manufacture' => 'value']
        )->where(
            'w.website_id = ?',
            $store->getWebsiteId()
        );

        $this->addFilter($store->getId(), 'visibility', $this->productVisibility->getVisibleInSiteIds(), 'in');
        $this->addFilter($store->getId(), 'status', $this->productStatus->getVisibleStatusIds(), 'in');

        // Join product images required attributes
        $imageIncludePolicy = $this->sitemapData->getProductImageIncludePolicy($store->getId());
        if (\Bss\XmlSiteMap\Model\Source\Product\Image\IncludeImage::INCLUDE_NONE != $imageIncludePolicy) {
            $this->joinAttribute($store->getId(), 'name', 'name');
            if (\Bss\XmlSiteMap\Model\Source\Product\Image\IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
                $this->joinAttribute($store->getId(), 'thumbnail', 'thumbnail');
            } elseif (\Bss\XmlSiteMap\Model\Source\Product\Image\IncludeImage::INCLUDE_BASE == $imageIncludePolicy) {
                $this->joinAttribute($store->getId(), 'image', 'image');
            }
        }

        return $connection->query($this->select);
    }

    /**
     * Filter category by store id
     *
     * @param int $storeId
     * @return array|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function categoryFilter($storeId)
    {
        /* @var $store Store */
        $store = $this->storeManager->getStore($storeId);
        if (!$store) {
            return false;
        }

        $connection = $this->getConnection();

        $selectCategory = $connection->select()->from(
            ['e' => $this->getTable('catalog_category_product')],
            ['category_id', 'product_id']
        )->joinInner(
            ['w' => $this->getTable('catalog_product_website')],
            'e.product_id = w.product_id',
            []
        )->where(
            'w.website_id = ?',
            $store->getWebsiteId()
        );

        $categoryQuery = $connection->query($selectCategory);

        $categoryArray = [];
        while ($rowCategory = $categoryQuery->fetch()) {
            $categoryArray['p_' . $rowCategory['product_id']][] = $rowCategory['category_id'];
        }
        return $categoryArray;
    }

    /**
     * @param object $row
     * @param array $arrayItemProduct
     * @return int
     */
    public function checkArrayItemCat($row, $arrayItemProduct)
    {
        $check = 0;
        foreach ($arrayItemProduct as $productId) {
            if ($row['entity_id'] == $productId) {
                $check = 1;
            }
        }
        if ($check == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Get product by store id
     *
     * @param string $devide
     * @param int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProduct($devide, $storeId)
    {
        $disableProductId = $this->sitemapData->getProductIdInclude($storeId);
        $stringItemProduct = (string)$disableProductId;
        $arrayItemProduct = explode(',', $stringItemProduct);

        $categoryArray = $this->categoryFilter($storeId);

        $disableTypeProduct = $this->sitemapData->getProductTypeInclude($storeId);
        $stringItem = (string)$disableTypeProduct;
        $stringItem = "," . $stringItem . ",";

        $query = $this->productFilter($storeId);
        $myProduct = [];
        while ($row = $query->fetch()) {
            try {
                $check = $this->checkArrayItemCat($row, $arrayItemProduct);
                $row['check'] = 0;
                if ($check == 0) {
                    $productType = $row['type_id'];
                    $productType = (string)$productType;
                    $stringItemValidate = strpos($stringItem, $productType);
                    if ($stringItemValidate == false && isset($categoryArray['p_' . $row['entity_id']])) {
                        $myProduct[$row['entity_id']] = $row;
                        $productCategoryArray = $categoryArray['p_' . $row['entity_id']];
                        $myProduct[$row['entity_id']]['category'] = $productCategoryArray[0];
                        if ($devide != 'none' && $devide !== null) {
                            $myProduct[$row['entity_id']]['manufacture'] = $row['manufacture'];
                        }
                        $myProduct[$row['entity_id']]['url'] = $this->processProductUrl(
                            $row,
                            $storeId,
                            $productCategoryArray
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e->getMessage());
            }
        }
        return $myProduct;
    }

    /**
     * @param array $row
     * @param int $storeId
     * @param array $categoryIdsObject
     * @return string
     */
    public function processProductUrl($row, $storeId, $categoryIdsObject)
    {
        $pathCheck = $this->sitemapData->isCheckPath($storeId);
        if ($pathCheck && !empty($categoryIdsObject)) {
            rsort($categoryIdsObject);
            $urlFinal = '';
            $productId = $row['entity_id'];
            foreach ($categoryIdsObject as $id) {
                $categoryUrl = $this->getProductCategoryUrl($productId, $id, $storeId);
                if ($categoryUrl) {
                    $urlFinal = $categoryUrl;
                    break;
                } else {
                    continue;
                }
            }
            if ($urlFinal) {
                return $urlFinal;
            }
        }
        return $row['url'];
    }

    /**
     * @param int $productId
     * @param int $categoryId
     * @param int $storeId
     * @return string
     */
    public function getProductCategoryUrl($productId, $categoryId, $storeId)
    {
        $targetPath = 'catalog/product/view/id/' . $productId . '/category/' . $categoryId;
        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::ENTITY_ID => $productId,
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::TARGET_PATH => $targetPath,
            UrlRewrite::STORE_ID => $storeId
        ]);
        if ($rewrite) {
            $productCategoryPath = $rewrite->getRequestPath();
            return $productCategoryPath;
        }
        return '';
    }

    /**
     * Get collection
     *
     * @param int $storeId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCollection($storeId)
    {
        $divideSiteMap = $this->sitemapData->getDevide($storeId);

        $products = [];

        /* @var $store Store */
        $store = $this->storeManager->getStore($storeId);
        if (!$store) {
            return false;
        }

        $myProduct = $this->getProduct($divideSiteMap, $storeId);

        arsort($myProduct);
        sort($myProduct);

        foreach ($myProduct as $key => $value) {
            switch ($divideSiteMap) {
                case 'date':
                    $value['check'] = $this->checkValueCategoryDate($key, $value, $myProduct);
                    break;
                case 'manufacture':
                    $value['check'] = $this->checkValueCategoryManufacture($key);
                    break;
                case 'category':
                    $value['check'] = $this->checkValueCategory($key, $value, $myProduct);
                    break;
                case 'none':
                    break;
            }

            $product = $this->productCollectionFactory->create(\Magento\Catalog\Model\Product::class);

            if (empty($value['url'])) {
                $value['url'] = 'catalog/product/view/id/' . $value[$this->getIdFieldName()];
            }

            $product->addData($value);
            $this->loadProductImages($product, $storeId);

            $products[$product->getId()] = $product;
        }
        return $products;
    }

    /**
     * Check category value
     *
     * @param int $key
     * @param string $value
     * @param string $myProduct
     * @return int
     */
    public function checkValueCategory($key, $value, $myProduct)
    {
        if ($key == 0) {
            return 1;
        } elseif ($key >=1) {
            $nextCategory = $myProduct[$key - 1]['category'];
            if ($nextCategory != $value['category']) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * @param int $key
     * @return int
     */
    public function checkValueCategoryManufacture($key)
    {
        if ($key == 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Check value category
     *
     * @param int $key
     * @param string $value
     * @param string $myProduct
     * @return int
     */
    public function checkValueCategoryDate($key, $value, $myProduct)
    {
        $time = substr($value['updated_at'], 0, 7);
        if ($key == 0) {
            return 1;
        } elseif ($key > 0) {
            $nextTime = substr($myProduct[$key - 1]['updated_at'], 0, 7);
            if ($nextTime != $time) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * Load product images
     *
     * @param \Magento\Framework\DataObject $product
     * @param int $storeId
     * @return void
     */
    public function loadProductImages($product, $storeId)
    {
        $imageIncludePolicy = $this->sitemapData->getProductImageIncludePolicy($storeId);

        // Get product images
        $imagesCollection = [];
        if (\Bss\XmlSiteMap\Model\Source\Product\Image\IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
            $imagesCollection = $this->getAllProductImages($product, $storeId);
        } elseif (\Bss\XmlSiteMap\Model\Source\Product\Image\IncludeImage::INCLUDE_BASE == $imageIncludePolicy) {
            if ($product->getImage() && $product->getImage() != self::NOT_SELECTED_IMAGE) {
                $imagesCollection = [
                    $this->dataObjectFactory->create()
                        ->addData(['url' => $this->getMediaConfig()->getBaseMediaUrlAddition() . $product->getImage()])
                ];
            } else {
                $imagesCollection = $this->getFirstProductImages($product, $storeId);
            }
        }

        if ($imagesCollection) {
            // Determine thumbnail path
            $thumbnail = $product->getThumbnail();
            if ($thumbnail && $product->getThumbnail() != self::NOT_SELECTED_IMAGE) {
                $thumbnail = $this->getMediaConfig()->getBaseMediaUrlAddition() . $thumbnail;
            } else {
                $thumbnail = $imagesCollection[0]->getUrl();
            }

            $product->setImages($this->dataObjectFactory->create()->addData(
                [
                    'collection' => $imagesCollection,
                    'title' => $product->getName(),
                    'thumbnail' => $thumbnail
                ]
            ));
        }
    }

    /**
     * Get all product images
     *
     * @param \Magento\Framework\DataObject $product
     * @param int $storeId
     * @return array
     */
    public function getAllProductImages($product, $storeId)
    {
        $product->setStoreId($storeId);
        $gallery = $this->mediaGalleryResourceModel->loadProductGalleryByAttributeId(
            $product,
            $this->mediaGalleryReadHandler->getAttribute()->getId()
        );

        $imagesCollection = [];
        if ($gallery) {
            $productMediaPath = $this->getMediaConfig()->getBaseMediaUrlAddition();
            foreach ($gallery as $image) {
                $imagesCollection[] = $this->dataObjectFactory->create()->addData(
                    [
                        'url' => $productMediaPath . $image['file'],
                        'caption' => $image['label'] ? $image['label'] : $image['label_default'],
                    ]
                );
            }
        }

        return $imagesCollection;
    }

    /**
     * Get all product images
     *
     * @param \Magento\Framework\DataObject $product
     * @param int $storeId
     * @return array
     */
    public function getFirstProductImages($product, $storeId)
    {
        $product->setStoreId($storeId);
        $gallery = $this->mediaGalleryResourceModel->loadProductGalleryByAttributeId(
            $product,
            $this->mediaGalleryReadHandler->getAttribute()->getId()
        );

        $imagesCollection = [];
        if ($gallery) {
            $productMediaPath = $this->getMediaConfig()->getBaseMediaUrlAddition();
            $countCollection = 0;
            foreach ($gallery as $image) {
                $countCollection++;
                if ($countCollection > 1) {
                    continue;
                }
                $imagesCollection[] = $this->dataObjectFactory->create()->addData(
                    [
                        'url' => $productMediaPath . $image['file'],
                        'caption' => $image['label'] ? $image['label'] : $image['label_default'],
                    ]
                );
            }
        }

        return $imagesCollection;
    }

    /**
     * Get media config
     *
     * @return \Magento\Catalog\Model\Product\Media\Config
     */
    public function getMediaConfig()
    {
        return $this->mediaConfig;
    }
}
