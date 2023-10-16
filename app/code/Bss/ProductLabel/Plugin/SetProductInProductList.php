<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Plugin;

/**
 * Class SetProductInProductList
 * @package Bss\ProductLabel\Plugin
 */
class SetProductInProductList
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetProductUrl(
        $subject,
        \Closure $proceed,
        $product,
        $additional = []
    ) {
        $this->registry->unregister('bss_init_product');
        $this->registry->unregister('bss_current_product');
        $this->registry->register('bss_current_product', $product);
        $returnValue = $proceed($product, $additional);
        return $returnValue;
    }
}
