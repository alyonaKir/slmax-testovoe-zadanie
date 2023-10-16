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
namespace Bss\MetaTagManager\Model;

/**
 * Class Rule
 * @package Bss\MetaTagManager\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    private $sqlBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    private $condCombineFactory;

    /**
     * @var array
     */
    protected $metaData = [];

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->condCombineFactory = $condCombineFactory;
        $this->productFactory = $productFactory;
        $this->sqlBuilder = $sqlBuilder;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Construct
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Bss\MetaTagManager\Model\ResourceModel\Rule::class);
        $this->setIdFieldName('id');
    }

    /**
     * Get Conditions Instance
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * @param array $data
     */
    public function setMetaData($data = [])
    {
        if (is_array($data) && !empty($data)) {
            $this->metaData = $data;
            $this->loadPost($data);
        }
    }

    /**
     * Get Actions Instance
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getActionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get Conditions Field Set Id
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset';
    }

    /**
     * Get Actions Field Set Id
     * @param string $formName
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($page = null)
    {
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($productCollection);
        $this->sqlBuilder->attachConditionToCollection($productCollection, $conditions);

        if ($page) {
            $pageSize = \Bss\SeoReport\Helper\Data::SEO_REPORT_CRAWL_MAX_URL;
            $productCollection->setPageSize($pageSize);
            $productCollection->setCurPage($page);
        }
        return $productCollection;
    }

    /**
     * @param object $product
     * @return bool
     */
    public function validateProductConditions($product)
    {
        $conditionsValidate = $this->getConditions()->validate($product);
        //Website Validate
        $productWebsites = $product->getWebsiteIds();
        $metaData = $this->metaData;
        if (isset($metaData['store'])) {
            $metaStore = $metaData['store'];
            //Check Proudct
            $metaStoreObject = explode(',', $metaStore);
            $statusCheckStore = $this->checkStoreInWebsite($metaStoreObject, $productWebsites);
            return $conditionsValidate && $statusCheckStore;
        }
        return $conditionsValidate;
    }

    /**
     * @param string $storeIds
     * @param string $websiteIds
     * @return bool
     */
    public function checkStoreInWebsite($storeIds, $websiteIds)
    {
        $websiteIdsCheck = [];
        $stores = $this->storeManager->getStores(false);
        foreach ($stores as $store) {
            $storeId = $store->getId();
            $websiteId = $store->getWebsiteId();
            if (in_array($storeId, $storeIds) && !in_array($websiteId, $websiteIdsCheck)) {
                $websiteIdsCheck[] = $websiteId;
            }
        }
        $statusReturn = false;
        if (!empty($websiteIdsCheck)) {
            foreach ($websiteIdsCheck as $value) {
                if (in_array($value, $websiteIds)) {
                    $statusReturn = true;
                }
            }
        }
        return $statusReturn;
    }
}
