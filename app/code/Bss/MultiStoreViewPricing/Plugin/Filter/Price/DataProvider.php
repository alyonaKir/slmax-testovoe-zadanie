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
namespace Bss\MultiStoreViewPricing\Plugin\Filter\Price;

class DataProvider
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
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject
     * @param \Magento\Framework\DB\Select $result
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetDataSet(\Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject, $result)
    {
        if (!$this->helper->isScopePrice()) {
            return $result;
        }
        
        $fromPart = $result->getPart(\Magento\Framework\DB\Select::FROM);
        if (isset($fromPart['main_table']) && $fromPart['main_table']['tableName'] == $this->resource->getTableName('catalog_product_index_price')) {
            $fromPart['main_table']['tableName'] = $this->resource->getTableName('catalog_product_index_price_store');
            $result->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
            $result->where('main_table.store_id = ?', $this->storeManager->getStore()->getStoreId());
        }
        return $result;
    }
}
