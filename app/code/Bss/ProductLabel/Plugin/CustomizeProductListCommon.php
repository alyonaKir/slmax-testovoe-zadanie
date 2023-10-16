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
 * Class CustomizeProductListCommon
 * @package Bss\ProductLabel\Plugin
 */
class CustomizeProductListCommon
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
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * CustomizeProductListCommon constructor.
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Bss\ProductLabel\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Bss\ProductLabel\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->helper = $helper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToHtml(\Magento\Catalog\Pricing\Render\FinalPriceBox $subject, $result)
    {
        if (!$this->helper->isEnable()) {
            return $result;
        }

        $product = $subject->getSaleableItem();
        $block = $this->layoutFactory->create()
            ->createBlock(\Bss\ProductLabel\Block\Label::class)
            ->setTemplate('Bss_ProductLabel::label_productlist.phtml')
            ->setProduct($product)
            ->toHtml();
        $result .= $block;

        return $result;
    }
}
