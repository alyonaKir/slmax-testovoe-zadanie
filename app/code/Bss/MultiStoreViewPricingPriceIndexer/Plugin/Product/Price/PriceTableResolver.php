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
namespace Bss\MultiStoreViewPricingPriceIndexer\Plugin\Product\Price;

class PriceTableResolver
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->resource = $resourceConnection;
    }

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver $subject
     * @param string $result
     * @return string
     */
    public function afterResolve(
        \Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver $subject,
        $result
    ) {
        if ($this->helper->isScopePrice()
            && $result == $this->resource->getTableName('catalog_product_index_price')) {
            return $this->resource->getTableName('catalog_product_index_price_store');
        }

        return $result;
    }
}
