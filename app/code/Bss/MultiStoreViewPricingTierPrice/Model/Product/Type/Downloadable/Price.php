<?php
namespace Bss\MultiStoreViewPricingTierPrice\Model\Product\Type\Downloadable;

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Price extends \Magento\Downloadable\Model\Product\Price
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null
    ) {
        $this->helper = $helper;
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $tierPriceExtensionFactory
        );
    }

    /**
     * Gets the 'tear_price' array from the product
     *
     * @param Product $product
     * @param string $key
     * @param bool $returnRawData
     * @return array
     */
    protected function getExistingPrices($product, $key, $returnRawData = false)
    {
        if (!$this->helper->isScopePrice()) {
            return parent::getExistingPrices($product, $key, $returnRawData);
        }

        $prices = null;

        $attribute = $product->getResource()->getAttribute($key);
        if ($attribute) {
            $attribute->getBackend()->afterLoad($product);
            $prices = $product->getData($key);
        }

        if ($prices === null || !is_array($prices)) {
            return ($returnRawData ? $prices : []);
        }

        return $prices;
    }
}
