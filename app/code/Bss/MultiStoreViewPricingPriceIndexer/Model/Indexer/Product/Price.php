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
namespace Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product;

use Magento\Framework\Indexer\CacheContext;

class Price extends \Magento\Catalog\Model\Indexer\Product\Price
{
    /**
     * @var \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Row
     */
    protected $_productPriceIndexerRow;

    /**
     * @var \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Rows
     */
    protected $_productPriceIndexerRows;

    /**
     * @var \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Full
     */
    protected $_productPriceIndexerFull;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;
    
    /**
     * @param Price\Action\Row  $productPriceIndexerRow
     * @param Price\Action\Rows $productPriceIndexerRows
     * @param Price\Action\Full $productPriceIndexerFull
     */
    public function __construct(
        \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Row $productPriceIndexerRow,
        \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Rows $productPriceIndexerRows,
        \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Full $productPriceIndexerFull
    ) {
        $this->_productPriceIndexerRow = $productPriceIndexerRow;
        $this->_productPriceIndexerRows = $productPriceIndexerRows;
        $this->_productPriceIndexerFull = $productPriceIndexerFull;
    }
}
