<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ShoppingCartRulePerStoreView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ShoppingCartRulePerStoreView\Model\Quote\Item;

/**
 * Quote item resource collection
 */
class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
{
    /**
     * Set Quote object to Collection
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->_quote = $quote;
        $quoteId = $quote->getId();
        if ($quoteId) {
            $this->addFieldToFilter('quote_id', $quote->getId());
            $this->_quote->collectTotals();
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }
}
