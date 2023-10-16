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
 * @package    Bss_MultiStoreViewPricingPriceIndexer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Framework\App\ObjectManager;

/**
 * Abstract action reindex class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Action extends \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory
     */
    private $dimensionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory $indexerPriceFactory
     * @param DefaultPrice $defaultIndexerResource
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice|null $tierPriceIndexResource
     * @param \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory|null $dimensionCollectionFactory
     * @param \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer|null $tableMaintainer
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory $indexerPriceFactory,
        DefaultPrice $defaultIndexerResource,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice $tierPriceIndexResource = null,
        \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory $dimensionCollectionFactory = null,
        \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $tableMaintainer = null
    ) {
        parent::__construct(
            $config,
            $storeManager,
            $currencyFactory,
            $localeDate,
            $dateTime,
            $catalogProductType,
            $indexerPriceFactory,
            $defaultIndexerResource,
            $tierPriceIndexResource,
            $dimensionCollectionFactory,
            $tableMaintainer
        );

        $this->dimensionCollectionFactory = $dimensionCollectionFactory ?? ObjectManager::getInstance()->get(
                \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory::class
            );
        $this->tableMaintainer = $tableMaintainer ?? ObjectManager::getInstance()->get(
                \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer::class
            );
    }

    /**
     * Execute action for given ids
     *
     * @param  array|int $ids
     * @return void
     */
    public function execute($ids)
    {
    }

    /**
     * Retrieve index table that will be used for write operations.
     *
     * This method is used during both partial and full reindex to identify the table.
     *
     * @return string
     */
    protected function getIndexTargetTable()
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::getIndexTargetTable();
        }

        return $this->_defaultIndexerResource->getTable('catalog_product_index_price_store');
    }

    /**
     * Refresh entities index
     *
     * @param array $changedIds
     * @return array Affected ids
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _reindexRows($changedIds = [])
    {
        $this->_prepareWebsiteDateTable();

        $productsTypes = $this->getProductsTypes($changedIds);
        $parentProductsTypes = $this->getParentProductsTypes($changedIds);

        $changedIds = array_merge($changedIds, ...array_values($parentProductsTypes));
        $productsTypes = array_merge_recursive($productsTypes, $parentProductsTypes);

        if ($changedIds) {
            $this->deleteIndexData($changedIds);
        }
        foreach ($productsTypes as $productType => $entityIds) {
            $indexer = $this->_getIndexer($productType);
            $this->_prepareTierPriceIndex($entityIds);
            if ($indexer instanceof \Magento\Framework\Indexer\DimensionalIndexerInterface) {
                foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
                    $this->tableMaintainer->createMainTmpTable($dimensions);
                    $temporaryTable = $this->tableMaintainer->getMainTmpTable($dimensions);
                    $this->_emptyTable($temporaryTable);
                    $indexer->executeByDimensions($dimensions, \SplFixedArray::fromArray($entityIds, false));
                    // copy to index
                    $this->_insertFromTable(
                        $temporaryTable,
                        $this->tableMaintainer->getMainTable($dimensions)
                    );
                }
            } else {
                // handle 3d-party indexers for backward compatibility
                $this->_emptyTable($this->_defaultIndexerResource->getIdxTable());
                $this->_copyRelationIndexData($entityIds);
                $indexer->reindexEntity($entityIds);
                $this->_syncData($entityIds);
            }
        }

        return $changedIds;
    }

    /**
     * Get products types.
     *
     * @param array $changedIds
     * @return array
     */
    private function getProductsTypes(array $changedIds = [])
    {
        $select = $this->getConnection()->select()->from(
            $this->_defaultIndexerResource->getTable('catalog_product_entity'),
            ['entity_id', 'type_id']
        );
        if ($changedIds) {
            $select->where('entity_id IN (?)', $changedIds);
        }
        $pairs = $this->getConnection()->fetchPairs($select);

        $byType = [];
        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        return $byType;
    }

    /**
     * Get parent products types
     * Used for add composite products to reindex if we have only simple products in changed ids set
     *
     * @param array $productsIds
     * @return array
     */
    private function getParentProductsTypes(array $productsIds)
    {
        $select = $this->getConnection()->select()->from(
            ['l' => $this->_defaultIndexerResource->getTable('catalog_product_relation')],
            ''
        )->join(
            ['e' => $this->_defaultIndexerResource->getTable('catalog_product_entity')],
            'e.' . $this->getProductIdFieldName() . ' = l.parent_id',
            ['e.entity_id as parent_id', 'type_id']
        )->where(
            'l.child_id IN(?)',
            $productsIds
        );
        $pairs = $this->getConnection()->fetchPairs($select);

        $byType = [];
        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        return $byType;
    }

    /**
     * @param array $entityIds
     * @return void
     */
    private function deleteIndexData(array $entityIds)
    {
        $select = $this->getConnection()->select()->from(
            ['index_price' => $this->getIndexTargetTable()],
            null
        )->where('index_price.entity_id IN (?)', $entityIds);
        $query = $select->deleteFromSelect('index_price');
        $this->getConnection()->query($query);
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->_defaultIndexerResource->getConnection();
    }
}
