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
namespace Bss\MultiStoreViewPricing\Observer\Product;

use Magento\Framework\Event\ObserverInterface;

class Collection implements ObserverInterface
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    public $helper;

    /**
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isScopePrice()) {
            return $this;
        }

        $collection = $observer->getCollection();
        $fromPart = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        if (isset($fromPart['price_index'])) {
            $indexTable = $collection->getTable('catalog_product_index_price');
            $indexTableStore = $collection->getTable('catalog_product_index_price_store');

            $select = $collection->getSelect();
            if ($fromPart['price_index']['tableName'] == $indexTable) {
                $fromPart['price_index']['tableName'] = $indexTableStore;
                $select->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
                $select->where('price_index.store_id = ?', $collection->getStoreId());
            }
        }

        return $this;
    }
}
