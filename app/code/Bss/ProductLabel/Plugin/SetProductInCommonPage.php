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
 * Class SetProductInCommonPage
 * @package Bss\ProductLabel\Plugin
 */
class SetProductInCommonPage
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
     * @param \Magento\Catalog\Model\Product $subject
     * @param \Closure $proceed
     * @param bool $useSid
     * @return string
     */
    public function aroundGetProductUrl(
        $subject,
        \Closure $proceed,
        $useSid = null
    ) {
        $returnValue = $proceed($useSid);
        $this->registry->unregister('bss_init_product');
        $this->registry->unregister('bss_current_product');
        $this->registry->register('bss_current_product', $subject);
        if ($this->registry->registry('current_product') !== null) {
            if ($this->registry->registry('current_product')->getId() == $subject->getId()) {
                $this->registry->register('bss_init_product', $this->registry->registry('current_product'));
            }
        }
        return $returnValue;
    }
}
