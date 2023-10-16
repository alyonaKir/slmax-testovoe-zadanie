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
 * @package    Bss_MultiStoreViewPricingTierPrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingTierPrice\Plugin\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

class Collection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{
    /**
     * Alias for main table
     */
    const MAIN_TABLE_ALIAS = 'e';

    /**
     * @var bool|string
     */
    private $linkField;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    private $backend;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    private $catalogProductFlatState;

    /**
     * Catalog Product Flat is enabled cache per store
     *
     * @var array
     */
    protected $flatEnabled = [];

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param null $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        $connection = null
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->catalogProductFlatState = $catalogProductFlatState;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $connection
        );
    }

    /**
     * Initialize resources
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _construct()
    {
        if ($this->isEnabledFlat()) {
            $this->_init(
                \Magento\Catalog\Model\Product::class,
                \Magento\Catalog\Model\ResourceModel\Product\Flat::class
            );
        } else {
            $this->_init(\Magento\Catalog\Model\Product::class, \Magento\Catalog\Model\ResourceModel\Product::class);
        }
    }

    /**
     * Set entity to use for attributes
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $entity
     * @return $this|\Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setEntity($entity)
    {
        if ($this->isEnabledFlat() && $entity instanceof \Magento\Framework\Model\ResourceModel\Db\AbstractDb) {
            $this->_entity = $entity;
            return $this;
        }
        return parent::setEntity($entity);
    }

    /**
     * Initialize collection select
     * Redeclared for remove entity_type_id condition
     * in catalog_product_entity we store just products
     *
     * @return $this
     */
    protected function _initSelect()
    {
        if ($this->isEnabledFlat()) {
            $this->getSelect()->from(
                [self::MAIN_TABLE_ALIAS => $this->getEntity()->getFlatTableName()],
                null
            )->columns(
                ['status' => new \Zend_Db_Expr(ProductStatus::STATUS_ENABLED)]
            );
            $this->addAttributeToSelect($this->getResource()->getDefaultAttributes());
            if ($this->catalogProductFlatState->getFlatIndexerHelper()->isAddChildData()) {
                $this->getSelect()->where('e.is_child=?', 0);
                $this->addAttributeToSelect(['child_id', 'is_child']);
            }
        } else {
            $this->getSelect()->from([self::MAIN_TABLE_ALIAS => $this->getEntity()->getEntityTable()]);
        }
        return $this;
    }

    /**
     * Load attributes into loaded entities
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this|\Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if ($this->isEnabledFlat()) {
            return $this;
        }
        return parent::_loadAttributes($printQuery, $logQuery);
    }

    /**
     * Add attribute to entities in collection. If $attribute=='*' select all attributes.
     *
     * @param array|string|integer|\Magento\Framework\App\Config\Element $attribute
     * @param bool|string $joinType
     * @return $this|\Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addAttributeToSelect($attribute, $joinType = false)
    {
        if ($this->isEnabledFlat()) {
            if (!is_array($attribute)) {
                $attribute = [$attribute];
            }
            foreach ($attribute as $attributeCode) {
                if ($attributeCode == '*') {
                    foreach ($this->getEntity()->getAllTableColumns() as $column) {
                        $this->getSelect()->columns('e.' . $column);
                        $this->_selectAttributes[$column] = $column;
                        $this->_staticFields[$column] = $column;
                    }
                } else {
                    $columns = $this->getEntity()->getAttributeForSelect($attributeCode);
                    if ($columns) {
                        foreach ($columns as $alias => $column) {
                            $this->getSelect()->columns([$alias => 'e.' . $column]);
                            $this->_selectAttributes[$column] = $column;
                            $this->_staticFields[$column] = $column;
                        }
                    }
                }
            }
            return $this;
        }
        return parent::addAttributeToSelect($attribute, $joinType);
    }

    /**
     * Retrieve Catalog Product Flat Helper object
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    public function getFlatState()
    {
        return $this->catalogProductFlatState;
    }

    /**
     * Retrieve is flat enabled flag
     * Return always false if magento run admin
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabledFlat()
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!isset($this->flatEnabled[$storeId])) {
            $this->flatEnabled[$storeId] = $this->getFlatState()->isAvailable();
        }
        return $this->flatEnabled[$storeId];
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param \Closure $proceed
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddTierPriceData($subject, $proceed)
    {
        if ($subject->getFlag('tier_price_added')) {
            return $subject;
        }

        $productIds = [];
        foreach ($subject->getItems() as $item) {
            $productIds[] = $item->getData($this->getLinkField());
        }
        if (!$productIds) {
            return $subject;
        }

        if ($this->helper->isScopePrice()) {
            $select = $this->getTierPriceStoreSelect($productIds, $subject);
        } else {
            $select = $this->getTierPriceSelect($productIds, $subject);
        }

        $this->fillTierPriceData($select);

        $subject->setFlag('tier_price_added', true);
        return $subject;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param \Closure $proceed
     * @param int $customerGroupId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddTierPriceDataByGroupId($subject, $proceed, $customerGroupId)
    {
        if ($subject->getFlag('tier_price_added')) {
            return $subject;
        }

        $productIds = [];
        foreach ($this->getItems() as $item) {
            $productIds[] = $item->getData($this->getLinkField());
        }
        if (!$productIds) {
            return $subject;
        }

        if ($this->helper->isScopePrice()) {
            $select = $this->getTierPriceStoreSelect($productIds, $subject);
        } else {
            $select = $this->getTierPriceSelect($productIds, $subject);
        }

        if ($this->helper->getTierPriceConfig() == '0') {
            $select->where(
                '(main_table.customer_group_id=? AND main_table.all_groups=0) OR main_table.all_groups=1',
                $customerGroupId
            );
        } else {
            $select->where(
                '(customer_group_id=? AND all_groups=0) OR all_groups=1',
                $customerGroupId
            );
        }
        $this->fillTierPriceData($select);

        $subject->setFlag('tier_price_added', true);
        return $subject;
    }

    /**
     * Get tier price select by product ids.
     *
     * @param array $productIds
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTierPriceSelect(array $productIds, $collection)
    {
        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = $this->getAttribute('tier_price');
        /* @var $backend \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice */
        $backend = $attribute->getBackend();
        $websiteId = 0;
        if (!$attribute->isScopeGlobal() && null !== $collection->getStoreId()) {
            $websiteId = $this->storeManager->getStore($collection->getStoreId())->getWebsiteId();
        }
        $select = $backend->getResource()->getSelect($websiteId);
        $select->columns(['product_id' => $this->getLinkField()])->where(
            $this->getLinkField() . ' IN(?)',
            $productIds
        )->order(
            $this->getLinkField()
        );
        return $select;
    }

    /**
     * @param array $productIds
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTierPriceStoreSelect(array $productIds, $collection)
    {
        $attribute = $this->getAttribute('tier_price_for_store');
        /* @var $backend \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice */
        $backend = $attribute->getBackend();
        $storeId = $collection->getStoreId();

        if ($this->helper->getTierPriceConfig() == '0') {
            $linkField = 'main_table.' . $this->getLinkField();
        } else {
            $linkField = $this->getLinkField();
        }

        $select = $backend->getResource()->getSelect($storeId);
        $select->columns(['product_id' => $this->getLinkField()])->where(
            $linkField . ' IN(?)',
            $productIds
        )->order(
            $linkField
        );
        return $select;
    }

    /**
     * Fill tier prices data.
     *
     * @param \Magento\Framework\DB\Select $select
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function fillTierPriceData(\Magento\Framework\DB\Select $select)
    {
        $tierPrices = [];
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $tierPrices[$row['product_id']][] = $row;
        }
        foreach ($this->getItems() as $item) {
            $productId = $item->getData($this->getLinkField());
            $this->getBackend()->setPriceData($item, isset($tierPrices[$productId]) ? $tierPrices[$productId] : []);
        }
    }

    /**
     * Retrieve link field and cache it.
     *
     * @return bool|string
     */
    private function getLinkField()
    {
        if ($this->linkField === null) {
            $this->linkField = $this->getConnection()->getAutoIncrementField($this->getTable('catalog_product_entity'));
        }
        return $this->linkField;
    }

    /**
     * Retrieve backend model and cache it.
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getBackend()
    {
        if ($this->backend === null) {
            $this->backend = $this->getAttribute('tier_price')->getBackend();
        }
        return $this->backend;
    }
}
