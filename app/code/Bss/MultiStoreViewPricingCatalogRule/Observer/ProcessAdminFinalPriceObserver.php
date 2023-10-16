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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category  BSS
 * @package   Bss_MultiStoreViewPricing
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;

class ProcessAdminFinalPriceObserver extends \Magento\CatalogRule\Observer\ProcessAdminFinalPriceObserver
{
    /**
     * Apply catalog price rules to product in admin
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return;
        }

        $product = $observer->getEvent()->getProduct();
        $storeId = $product->getStoreId();
        $date = $this->localeDate->scopeDate($storeId);
        $key = false;

        $ruleData = $this->coreRegistry->registry('rule_data');
        if ($ruleData) {
            $wId = $ruleData->getWebsiteId();
            $gId = $ruleData->getCustomerGroupId();
            $pId = $product->getId();

            $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}|{$storeId}";
        } elseif ($product->getWebsiteId() !== null && $product->getCustomerGroupId() !== null) {
            $wId = $product->getWebsiteId();
            $gId = $product->getCustomerGroupId();
            $pId = $product->getId();
            $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}|{$storeId}";
        }

        if ($key) {
            if (!$this->rulePricesStorage->hasRulePrice($key)) {
                $rulePrice = $this->resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId, $storeId);
                $this->rulePricesStorage->setRulePrice($key, $rulePrice);
            }
            if ($this->rulePricesStorage->getRulePrice($key) !== false) {
                $finalPrice = min($product->getData('final_price'), $this->rulePricesStorage->getRulePrice($key));
                $product->setFinalPrice($finalPrice);
            }
        }

        return $this;
    }
}
