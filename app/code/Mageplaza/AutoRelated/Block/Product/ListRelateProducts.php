<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class ListRelateProducts
 * @package Mageplaza\AutoRelated\Block\Product
 */
class ListRelateProducts extends ListProduct
{
    /**
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        return $this->_productCollection;
    }

    /**
     * @param AbstractCollection $collection
     *
     * @return void
     */
    public function setProductCollection(AbstractCollection $collection)
    {
        $this->_productCollection = $collection;
    }
}
