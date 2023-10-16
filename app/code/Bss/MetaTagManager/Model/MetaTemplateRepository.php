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

use Bss\MetaTagManager\Api\Data\MetaTemplateInterfaceFactory;
use Bss\MetaTagManager\Api\Data\MetaTemplateSearchResultsInterfaceFactory;
use Bss\MetaTagManager\Api\MetaTemplateRepositoryInterface;
use Bss\MetaTagManager\Model\ResourceModel\MetaTemplate as ResourceMetaTemplate;
use Bss\MetaTagManager\Model\ResourceModel\MetaTemplate\CollectionFactory
    as MetaTemplateCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class MetaTemplateRepository
 * @package Bss\MetaTagManager\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetaTemplateRepository implements MetaTemplateRepositoryInterface
{
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var MetaTemplateSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceMetaTemplate
     */
    protected $resource;

    /**
     * @var MetaTemplateFactory
     */
    protected $metaTemplateCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var MetaTemplateInterfaceFactory
     */
    protected $dataMetaTemplateFactory;

    /**
     * @var MetaTemplateFactory
     */
    protected $metaTemplateFactory;

    /**
     * @param ResourceMetaTemplate $resource
     * @param MetaTemplateFactory $metaTemplateFactory
     * @param MetaTemplateInterfaceFactory $dataMetaTemplateFactory
     * @param MetaTemplateCollectionFactory $metaTemplateCollectionFactory
     * @param MetaTemplateSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ResourceMetaTemplate $resource,
        MetaTemplateFactory $metaTemplateFactory,
        MetaTemplateInterfaceFactory $dataMetaTemplateFactory,
        MetaTemplateCollectionFactory $metaTemplateCollectionFactory,
        MetaTemplateSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->metaTemplateFactory = $metaTemplateFactory;
        $this->metaTemplateCollectionFactory = $metaTemplateCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataMetaTemplateFactory = $dataMetaTemplateFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * @param \Bss\MetaTagManager\Api\Data\MetaTemplateInterface $metaTemplate
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(
        \Bss\MetaTagManager\Api\Data\MetaTemplateInterface $metaTemplate
    ) {
        $this->resource->save($metaTemplate);
        return $metaTemplate;
    }

    /**
     * @param string $id
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface|MetaTemplate
     */
    public function getById($id)
    {
        $metaTemplate = $this->metaTemplateFactory->create();
        $metaTemplate->load($id);
        return $metaTemplate;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->metaTemplateCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $items = [];

        foreach ($collection as $metaTemplateModel) {
            $metaTemplateData = $this->dataMetaTemplateFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $metaTemplateData,
                $metaTemplateModel->getData(),
                'Bss\MetaTagManager\Api\Data\MetaTemplateInterface'
            );
            $items[] = $this->dataObjectProcessor->buildOutputDataArray(
                $metaTemplateData,
                'Bss\MetaTagManager\Api\Data\MetaTemplateInterface'
            );
        }
        $searchResults->setItems($items);
        return $searchResults;
    }

    /**
     * @param \Bss\MetaTagManager\Api\Data\MetaTemplateInterface $metaTemplate
     * @return bool
     * @throws \Exception
     */
    public function delete(
        \Bss\MetaTagManager\Api\Data\MetaTemplateInterface $metaTemplate
    ) {
        $this->resource->delete($metaTemplate);
        return true;
    }

    /**
     * @param string $id
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
