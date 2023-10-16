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
 * @package    Bss_ProductLabel
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Plugin\Catalog\Ui;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;

/**
 * Collect additional information about product, in order to allow product rendering on front
 */
class AdditionalInfo
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductRender\ProductRenderExtensionInterfaceFactory
     */
    private $productRenderExtensionFactory;

    /**
     * @var \Bss\ProductLabel\Block\Label
     */
    private $label;

    /**
     * @var \Bss\ProductLabel\Helper\Data
     */
    private $helper;

    /**
     * AdditionalInfo constructor.
     * @param \Magento\Catalog\Api\Data\ProductRenderExtensionFactory $productRenderExtensionFactory
     * @param \Bss\ProductLabel\Block\Label $label
     * @param \Bss\ProductLabel\Helper\Data $helper
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductRenderExtensionFactory $productRenderExtensionFactory,
        \Bss\ProductLabel\Block\Label $label,
        \Bss\ProductLabel\Helper\Data $helper
    ) {
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
        $this->label = $label;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\AdditionalInfo $subject
     * @param \Closure $proceed
     * @param ProductInterface $product
     * @param ProductRenderInterface $productRender
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollect(
        $subject,
        \Closure $proceed,
        ProductInterface $product,
        ProductRenderInterface $productRender
    ) {
        $returnValue = $proceed($product, $productRender);
        if (!$this->helper->isEnable()) {
            return $returnValue;
        }

        /** @var \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes */
        $extensionAttributes = $productRender->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }
        $extensionAttributes->setLabelData($this->label->getLabelData($product));
        $productRender->setExtensionAttributes($extensionAttributes);

        return $returnValue;
    }
}
