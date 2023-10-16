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
 * Class CustomizeCartCommonPage
 * @package Bss\ProductLabel\Plugin
 */
class CustomizeCartCommonPage
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Bss\ProductLabel\Helper\Data
     */
    private $helper;

    /**
     * CustomizeProductListCommon constructor.
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Bss\ProductLabel\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Bss\ProductLabel\Helper\Data $helper
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Item\Renderer\Actions $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToHtml(\Magento\Checkout\Block\Cart\Item\Renderer\Actions $subject, $result)
    {
        if (!$this->helper->isEnable()) {
            return $result;
        }

        $product = $subject->getItem()->getProduct();
        $block = $this->layoutFactory->create()
            ->createBlock(\Bss\ProductLabel\Block\Label::class)
            ->setTemplate('Bss_ProductLabel::label_productlist.phtml')
            ->setProduct($product)
            ->toHtml();
        $result .= $block;

        return $result;
    }
}
