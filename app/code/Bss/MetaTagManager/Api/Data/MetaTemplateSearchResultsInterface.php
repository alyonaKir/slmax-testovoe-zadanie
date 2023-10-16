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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Api\Data;

/**
 * Interface MetaTemplateSearchResultsInterface
 * @package Bss\MetaTagManager\Api\Data
 */
interface MetaTemplateSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get product_meta_template list.
     *
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     *
     * @param \Bss\MetaTagManager\Api\Data\MetaTemplateInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
