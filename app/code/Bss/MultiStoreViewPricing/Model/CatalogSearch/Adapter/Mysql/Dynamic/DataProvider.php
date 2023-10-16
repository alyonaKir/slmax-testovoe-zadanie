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
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Model\CatalogSearch\Adapter\Mysql\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface as MysqlDataProviderInterface;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManager;
use \Magento\Framework\Search\Request\IndexScopeResolverInterface;

class DataProvider extends \Magento\CatalogSearch\Model\Adapter\Mysql\Dynamic\DataProvider
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var Range
     */
    private $range;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var MysqlDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var IntervalFactory
     */
    private $intervalFactory;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var IndexScopeResolverInterface
     */
    private $priceTableResolver;

    /**
     * @var DimensionFactory|null
     */
    private $dimensionFactory;

    /**
     * @param ResourceConnection $resource
     * @param Range $range
     * @param Session $customerSession
     * @param MysqlDataProviderInterface $dataProvider
     * @param IntervalFactory $intervalFactory
     * @param StoreManager $storeManager
     * @param IndexScopeResolverInterface|null $priceTableResolver
     * @param DimensionFactory|null $dimensionFactory
     */
    public function __construct(
        ResourceConnection $resource,
        Range $range,
        Session $customerSession,
        MysqlDataProviderInterface $dataProvider,
        IntervalFactory $intervalFactory,
        StoreManager $storeManager = null,
        IndexScopeResolverInterface $priceTableResolver = null,
        DimensionFactory $dimensionFactory = null
    ) {
        parent::__construct(
            $resource,
            $range,
            $customerSession,
            $dataProvider,
            $intervalFactory,
            $storeManager,
            $priceTableResolver,
            $dimensionFactory
        );

        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->range = $range;
        $this->customerSession = $customerSession;
        $this->dataProvider = $dataProvider;
        $this->intervalFactory = $intervalFactory;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManager::class);
        $this->priceTableResolver = $priceTableResolver ?: ObjectManager::getInstance()->get(
            IndexScopeResolverInterface::class
        );
        $this->dimensionFactory = $dimensionFactory ?: ObjectManager::getInstance()->get(DimensionFactory::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations(\Magento\Framework\Search\Dynamic\EntityStorage $entityStorage)
    {
        $aggregation = [
            'count' => 'count(main_table.entity_id)',
            'max' => 'MAX(min_price)',
            'min' => 'MIN(min_price)',
            'std' => 'STDDEV_SAMP(min_price)',
        ];

        $select = $this->getSelect();
        $storeId = $this->storeManager->getStore()->getId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        $tableName = $this->priceTableResolver->resolve(
            'catalog_product_index_price_store',
            []
        );
        /** @var Table $table */
        $table = $entityStorage->getSource();
        $select->from(['main_table' => $tableName], [])
            ->where('main_table.entity_id in (select entity_id from ' . $table->getName() . ')')
            ->columns($aggregation);

        $select->where('customer_group_id = ?', $customerGroupId);
        $select->where('main_table.store_id = ?', $storeId);

        return $this->connection->fetchRow($select);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $select = $this->dataProvider->getDataSet($bucket, $dimensions, $entityStorage->getSource());
        $column = $select->getPart(Select::COLUMNS)[0];
        $select->reset(Select::COLUMNS);
        $rangeExpr = new \Zend_Db_Expr(
            $this->connection->getIfNullSql(
                $this->connection->quoteInto('FLOOR(' . $column[1] . ' / ? ) + 1', $range),
                1
            )
        );

        $select
            ->columns(['range' => $rangeExpr])
            ->columns(['metrix' => 'COUNT(*)'])
            ->group('range')
            ->order('range');

        $storeId = $this->storeManager->getStore()->getId();
        $select->where('main_table.store_id = ?', $storeId);

        $result = $this->connection->fetchPairs($select);

        return $result;
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
