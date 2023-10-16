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
namespace Bss\MultiStoreViewPricing\Plugin\CatalogSearch;

class TableMapper
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    public $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Search\TableMapper $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterAddTables(\Magento\CatalogSearch\Model\Search\TableMapper $subject, $result)
    {
        if (!$this->helper->isScopePrice()) {
            return $result;
        }
        
        $fromPart = $result->getPart(\Magento\Framework\DB\Select::FROM);
        if (isset($fromPart['price_index']) &&
            $fromPart['price_index']['tableName'] == $this->resource->getTableName('catalog_product_index_price')) {
            $fromPart['price_index']['tableName'] = $this->resource->getTableName('catalog_product_index_price_store');
            $result->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
            $result->where('price_index.store_id = ?', $this->storeManager->getStore()->getStoreId());
        }
        return $result;
    }
}
