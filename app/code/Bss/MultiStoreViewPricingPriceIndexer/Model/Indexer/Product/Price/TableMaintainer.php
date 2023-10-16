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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;

class TableMaintainer extends \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer
{
    /**
     * Catalog product price store index table name
     */
    const MAIN_INDEX_TABLE = 'catalog_product_index_price_store';

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Catalog tmp category index table name
     */
    private $tmpTableSuffix = '_temp';

    /**
     * @var string[]
     */
    private $mainTmpTable;

    /**
     * @param ResourceConnection $resource
     * @param TableResolver $tableResolver
     * @param null $connectionName
     */
    public function __construct(
        ResourceConnection $resource,
        TableResolver $tableResolver,
        $connectionName = null
    ) {
        parent::__construct($resource, $tableResolver, $connectionName);
        $this->resource = $resource;
        $this->tableResolver = $tableResolver;
    }

    /**
     * Return main index table name
     *
     * @param Dimension[] $dimensions
     *
     * @return string
     */
    public function getMainTable(array $dimensions): string
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $dimensions);
    }

    /**
     * Create temporary index table for dimensions
     *
     * @param Dimension[] $dimensions
     *
     * @return void
     */
    public function createMainTmpTable(array $dimensions)
    {
        // Create temporary table based on template table catalog_product_index_price_tmp without indexes
        $templateTableName = $this->resource->getTableName(self::MAIN_INDEX_TABLE . '_tmp');
        $temporaryTableName = $this->getMainTable($dimensions) . $this->tmpTableSuffix;
        $this->getConnection()->createTemporaryTableLike($temporaryTableName, $templateTableName, true);
        $this->mainTmpTable[$this->getArrayKeyForTmpTable($dimensions)] = $temporaryTableName;
    }

    /**
     * Return temporary index table name
     *
     * @param Dimension[] $dimensions
     *
     * @return string
     *
     * @throws \LogicException
     */
    public function getMainTmpTable(array $dimensions): string
    {
        $cacheKey = $this->getArrayKeyForTmpTable($dimensions);
        if (!isset($this->mainTmpTable[$cacheKey])) {
            throw new \LogicException(
                sprintf('Temporary table for provided dimensions "%s" does not exist', $cacheKey)
            );
        }
        return $this->mainTmpTable[$cacheKey];
    }

    /**
     * Get array key for tmp table
     *
     * @param Dimension[] $dimensions
     *
     * @return string
     */
    private function getArrayKeyForTmpTable(array $dimensions): string
    {
        $key = 'temp';
        foreach ($dimensions as $dimension) {
            $key .= $dimension->getName() . '_' . $dimension->getValue();
        }
        return $key;
    }
}
