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
namespace Bss\MultiStoreViewPricing\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Framework\App\ObjectManager;

/**
 * Strategy which processes exclusions from general rules
 */
class ExclusionStrategy extends \Magento\CatalogSearch\Model\Search\FilterMapper\ExclusionStrategy
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * List of fields that can be processed by exclusion strategy
     * @var array
     */
    private $validFields = ['price', 'category_ids'];

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AliasResolver $aliasResolver
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AliasResolver $aliasResolver
    ) {
        parent::__construct(
            $resourceConnection,
            $storeManager,
            $aliasResolver
        );
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->aliasResolver = $aliasResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $helper = ObjectManager::getInstance()->get(\Bss\MultiStoreViewPricing\Helper\Data::class);
        if (!$helper->isScopePrice()) {
            return parent::apply($filter, $select);
        }

        if (!in_array($filter->getField(), $this->validFields, true)) {
            return false;
        }

        if ($filter->getField() === 'price') {
            return $this->applyPriceFilter($filter, $select);
        } elseif ($filter->getField() === 'category_ids') {
            return $this->applyCategoryFilter($filter, $select);
        }
    }

    /**
     * Applies filter bt price field
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyPriceFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $helper = ObjectManager::getInstance()->get(\Bss\MultiStoreViewPricing\Helper\Data::class);
        if (!$helper->isScopePrice()) {
            return $this->applyPriceFilterCustom($filter, $select);
        }

        $alias = $this->aliasResolver->getAlias($filter);
        $tableName = $this->resourceConnection->getTableName('catalog_product_index_price_store');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf('%s.entity_id = price_index.entity_id AND price_index.website_id = ? AND price_index.store_id = '. $this->storeManager->getStore()->getId(), $mainTableAlias),
                $this->storeManager->getWebsite()->getId()
            ),
            []
        );
        return true;
    }

    /**
     * Applies filter by category
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyCategoryFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $helper = ObjectManager::getInstance()->get(\Bss\MultiStoreViewPricing\Helper\Data::class);
        if (!$helper->isScopePrice()) {
            return $this->applyCategoryFilterCustom($filter, $select);
        }

        $alias = $this->aliasResolver->getAlias($filter);
        $tableName = $this->resourceConnection->getTableName('catalog_category_product_index');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf(
                    '%s.entity_id = category_ids_index.product_id AND category_ids_index.store_id = ?',
                    $mainTableAlias
                ),
                $this->storeManager->getStore()->getId()
            ),
            []
        );

        return true;
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return string|null
     */
    private function extractTableAliasFromSelect(\Magento\Framework\DB\Select $select)
    {
        $helper = ObjectManager::getInstance()->get(\Bss\MultiStoreViewPricing\Helper\Data::class);
        if (!$helper->isScopePrice()) {
            return $this->extractTableAliasFromSelectCustom($select);
        }

        $fromArr = array_filter(
            $select->getPart(\Magento\Framework\DB\Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === \Magento\Framework\DB\Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }

    /**
     * Applies filter bt price field
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function applyPriceFilterCustom(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $this->aliasResolver->getAlias($filter);
        $tableName = $this->resourceConnection->getTableName('catalog_product_index_price');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf('%s.entity_id = price_index.entity_id AND price_index.website_id = ?', $mainTableAlias),
                $this->storeManager->getWebsite()->getId()
            ),
            []
        );

        return true;
    }

    /**
     * Applies filter by category
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function applyCategoryFilterCustom(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $this->aliasResolver->getAlias($filter);
        $tableName = $this->resourceConnection->getTableName('catalog_category_product_index');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf(
                    '%s.entity_id = category_ids_index.product_id AND category_ids_index.store_id = ?',
                    $mainTableAlias
                ),
                $this->storeManager->getStore()->getId()
            ),
            []
        );

        return true;
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return string|null
     */
    private function extractTableAliasFromSelectCustom(\Magento\Framework\DB\Select $select)
    {
        $fromArr = array_filter(
            $select->getPart(\Magento\Framework\DB\Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === \Magento\Framework\DB\Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
