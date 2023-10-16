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
 * @category  BSS
 * @package   Bss_MultiStoreViewPricing
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Model\Indexer;

use Magento\Framework\App\ObjectManager;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;

/**
 * Persist product prices to index table.
 */
class RuleProductPricesPersistor extends \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateFormat;

    /**
     * @var TableSwapper
     */
    private $tableSwapper;

    /**
     * @param \Magento\Framework\Stdlib\DateTime $dateFormat
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher
     * @param TableSwapper|null $tableSwapper
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher,
        TableSwapper $tableSwapper = null
    ) {
        parent::__construct(
            $dateFormat,
            $resource,
            $activeTableSwitcher
        );
        $this->dateFormat = $dateFormat;
        $this->resource = $resource;
        $this->tableSwapper = $tableSwapper ??
            ObjectManager::getInstance()->get(TableSwapper::class);
    }

    /**
     * Persist prices data to index table.
     *
     * @param array $priceData
     * @param bool $useAdditionalTable
     * @return bool
     * @throws \Exception
     */
    public function execute(array $priceData, $useAdditionalTable = false)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::execute($priceData, $useAdditionalTable);
        }
        
        if (empty($priceData)) {
            return false;
        }

        $connection = $this->resource->getConnection();
        $indexTable = $this->resource->getTableName('catalogrule_product_price_store');
        if ($useAdditionalTable) {
            $indexTable = $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_product_price_store')
            );
        }

        $productIds = [];

        try {
            foreach ($priceData as $key => $data) {
                $productIds['product_id'] = $data['product_id'];
                $priceData[$key]['rule_date'] = $this->dateFormat->formatDate($data['rule_date'], false);
                $priceData[$key]['latest_start_date'] = $this->dateFormat->formatDate(
                    $data['latest_start_date'],
                    false
                );
                $priceData[$key]['earliest_end_date'] = $this->dateFormat->formatDate(
                    $data['earliest_end_date'],
                    false
                );
            }
            $connection->insertOnDuplicate($indexTable, $priceData);
        } catch (\Exception $e) {
            throw $e;
        }
        return true;
    }
}
