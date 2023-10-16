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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Model\ResourceModel\ReportLinks\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Class Collection
 * @package Bss\SeoReport\Model\ResourceModel\ReportLinks\Grid
 */
class Collection extends \Bss\SeoReport\Model\ResourceModel\ReportLinks\Collection implements SearchResultInterface
{
    /**
     * @var $aggregations
     */
    protected $aggregations;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public $_eventPrefix;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public $_eventObject;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param $eventPrefix
     * @param $eventObject
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $eventPrefix,
        $eventObject,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \Bss\SeoReport\Model\ResourceModel\ReportLinks::class
        );
    }

    /**
     * @inheritDoc
     *
     * @return \Magento\Framework\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @inheritDoc
     *
     * @param \Magento\Framework\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return \Bss\SeoReport\Model\ResourceModel\ReportLinks\Collection|void
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'request_path') {
            if (is_array($condition)) {
                foreach ($condition as $key => $value) {
                    if ($key === 'like') {
                        $condition[$key] = $this->processResquestPath($value);
                    }
                }
            }
        }
        parent::addFieldToFilter($field, $condition);
    }

    /**
     * @param $requestPath
     * @return mixed
     */
    public function processResquestPath($requestPath)
    {
        $stores = $this->storeManager->getStores(false);
        $baseUrlObject = [];
        foreach ($stores as $store) {
            $baseUrl = $store->getBaseUrl();
            $baseUrlObject[] = $baseUrl;
        }
        usort($baseUrlObject, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach ($baseUrlObject as $urlAfter) {
            $requestPath = str_replace($urlAfter, "", $requestPath);
        }
        return $requestPath;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
