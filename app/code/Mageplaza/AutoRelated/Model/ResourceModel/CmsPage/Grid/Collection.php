<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Model\ResourceModel\CmsPage\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\ResourceModel\Page\Grid\Collection as CmsPageCollection;
use Mageplaza\AutoRelated\Model\ResourceModel\CmsPageRule\CollectionFactory as ARPCmsCollection;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package Mageplaza\AutoRelated\Model\ResourceModel\CmsPage\Grid
 */
class Collection extends CmsPageCollection
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ARPCmsCollection
     */
    private $arpCmsCollection;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param mixed|null $mainTable
     * @param string $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param Registry $registry
     * @param ARPCmsCollection $arpCmsCollection
     * @param string $model
     * @param null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        Registry $registry,
        ARPCmsCollection $arpCmsCollection,
        $model = Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        $this->registry         = $registry;
        $this->arpCmsCollection = $arpCmsCollection;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model,
            $connection,
            $resource
        );
    }

    /**
     * @return $this|Collection|void
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $rule = $this->registry->registry('autorelated_rule');
        if ($ruleId = $rule->getId()) {
            $arpCollection = $this->arpCmsCollection->create()->addFieldToFilter('rule_id', $ruleId);
            $select        = $this->getSelect();
            $select->joinLeft(
                ['arp_cms' => $arpCollection->getSelect()],
                'main_table.page_id = arp_cms.page_id',
                ['position' => 'IF(position IS NOT NULL, position, \'top\')']
            )->columns([
                'page_id_checkbox' => 'IF(position IS NOT NULL, 1, 0)'
            ])->where(
                'main_table.is_active = ?',
                1
            );
        }

        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return $this|CmsPageCollection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (($field === 'page_id_checkbox')) {
            $resultCondition = $this->_translateCondition($field, $condition);
            $this->getSelect()->having($resultCondition);

            return $this;
        }

        return parent::addFieldToFilter($field, $condition); // TODO: Change the autogenerated stub
    }

    /**
     * @return Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        return clone $this->getSelect();
    }
}
