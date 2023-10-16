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
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Plugin\Quote\Validator\MinimumOrderAmount;

class ValidationMessage
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $currency;

    /**
     * ValidationMessage constructor.
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Locale\CurrencyInterface $currency
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->currency = $currency;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage $subject
     * @param $proceed
     * @return \Magento\Framework\Phrase|mixed
     * @throws \Zend_Currency_Exception
     */
    public function aroundGetMessage($subject, $proceed)
    {
        if (!$this->helper->isScopePrice()) {
            return $proceed();
        }

        $message = $this->scopeConfig->getValue(
            'sales/minimum_order/description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$message) {
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();

            $minAmount = $this->scopeConfig->getValue(
                'sales/minimum_order/amount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $storeId = $this->storeManager->getStore()->getId();
            $store = $this->storeManager->getStore($storeId);
            $toCurrency = $store->getCurrentCurrencyCode();
            $minAmount = $store->getBaseCurrency()->convert($minAmount, $toCurrency);

            $minimumAmount = $this->currency->getCurrency($currencyCode)->toCurrency(
                $minAmount
            );
            $message = __('Minimum order amount is %1', $minimumAmount);
        } else {
            $message = __($message);
        }

        return $message;
    }
}
