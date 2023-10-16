<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bss\PriceCustomize\Model\ConfigurableProduct;

use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;

class FinalPrice extends \Magento\ConfigurableProduct\Pricing\Price\FinalPrice
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     * @param float $quantity
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface $priceResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        $quantity,
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface $priceResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency, $priceResolver);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        if ($this->getIsCustomizePrice($this->product)) {
            $this->product->setIsCustomize(true);
            if (!isset($this->values[$this->product->getId()])) {
                $customSpecialPrice = $this->product->getSpecialPrice();
                $price = null;
                foreach ($this->product->getTypeInstance()->getSalableUsedProducts($this->product) as $subProduct) {
                    $productPrice = $subProduct->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();
                    $price = $price ? max($price, $productPrice) : $productPrice;
                }
                $customPrice = $this->product->getData('price');
                if ($this->product->getSpecialPrice() && $this->product->getTypeId() == "configurable") {
                    if ($customSpecialPrice != null
                        && $customSpecialPrice != ''
                        && $customSpecialPrice < $customPrice) {
                        $this->product->setNormalPrice($customPrice);
                        $this->product->setCustomSpecialPrice($customSpecialPrice);
                        $this->values[$this->product->getId()] = $customSpecialPrice;
                    }
                } else {
                    $this->product->setNormalPrice($price);
                    $this->values[$this->product->getId()] = (float)$price;
                }
                return (float)$customSpecialPrice;
            }
            return $this->values[$this->product->getId()];
        }
        return parent::getValue();
    }

    /**
     * @param $product
     * @return bool
     */
    private function getIsCustomizePrice($product)
    {
        $configAttributeSet = $this->scopeConfig->getValue(
            'price_customize/general/attribute_set',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $arrayConfigAttributeSet = explode(",", $configAttributeSet);
        foreach ($arrayConfigAttributeSet as $attributeIdConfig) {
            if ($product->getAttributeSetId() == $attributeIdConfig) {
                return true;
            }
        }
        return false;
    }
}
