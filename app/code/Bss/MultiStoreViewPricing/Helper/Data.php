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
namespace Bss\MultiStoreViewPricing\Helper;

/**
 * MultiStoreViewPricing Observer
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $scopeConfig;
    public $state;
    public $request;
    public $entityAttribute;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->request = $request;
        $this->entityAttribute = $entityAttribute;
    }

    public function isScopePrice()
    {
        $active =  $this->scopeConfig->getValue(
            'catalog/price/scope',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($active == 2) {
            return true;
        }
        
        return false;
    }

    public function getProductAttributeId($attributeCode) {
        $attribute = $this->entityAttribute->loadByCode('catalog_product', $attributeCode);
        if($attribute && $attribute->getId() != '') 
            return $attribute->getId();
        return null;
    }

    public function isAdmin()
    {
        return 'adminhtml' == $this->state->getAreaCode();
    }

    public function checkRoute()
    {
        return $this->request->getModuleName().'_'.$this->request->getControllerName();
    }

    public function getTierPriceConfig()
    {
        $value =  $this->scopeConfig->getValue(
            'multistoreviewpricing/general/tier_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $value;
    }
}
