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
 * Class SetProductInWishlist
 * @package Bss\ProductLabel\Plugin
 */
class SetProductInWishlist
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
     * @param \Magento\Wishlist\Model\Item $item
     * @param \Magento\Catalog\Model\Product $result
     * @return \Magento\Catalog\Model\Product
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProduct($item, $result)
    {
        $this->registry->unregister('bss_init_product');
        $this->registry->unregister('bss_current_product');
        $this->registry->register('bss_current_product', $result);
        return $result;
    }
}
