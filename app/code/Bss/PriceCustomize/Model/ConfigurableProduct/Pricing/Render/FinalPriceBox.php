<?php

namespace Bss\PriceCustomize\Model\ConfigurableProduct\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Bss\PriceCustomize\Model\ConfigurableProduct\FinalPrice;

/**
 * Class FinalPriceBox
 *
 * @package Bss\PriceCustomize\Model\ConfigurableProduct\Pricing\Render
 */
class FinalPriceBox extends \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox
{
    /**
     * @var LowestPriceOptionsProviderInterface|null
     */
    protected $lowestPriceOptionsProvider;

    /**
     * FinalPriceBox constructor.
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     * @param array $data
     * @param LowestPriceOptionsProviderInterface|null $lowestPriceOptionsProvider
     * @param SalableResolverInterface|null $salableResolver
     * @param MinimalPriceCalculatorInterface|null $minimalPriceCalculator
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider,
        array $data = [],
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null,
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider;
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $configurableOptionsProvider,
            $data,
            $lowestPriceOptionsProvider,
            $salableResolver,
            $minimalPriceCalculator
        );
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);
    }

    /**
     * Define if the special price should be shown
     *
     * @return bool
     */
    public function hasSpecialPrice()
    {
        $product = $this->getSaleableItem();
        $isCustomize = $product->getIsCustomize();
        if ($isCustomize) {
            $price = (float)$product->getNormalPrice();
            $specialPrice = (float)$product->getCustomSpecialPrice();
            if ($specialPrice != null && $specialPrice < $price) {
                return true;
            }
            return false;
        }
        return parent::hasSpecialPrice();
    }
}
