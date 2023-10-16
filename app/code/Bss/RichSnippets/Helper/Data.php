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
 * @category   BSS
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $urlInterface;

    /**
     * @var string
     */
    public $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->urlInterface = $context->getUrlBuilder();
    }

    /**
     * @return mixed
     */
    public function getEnable()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/general/enable', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getSearchBox()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/search_box', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getWebsiteName()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/website_name', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableWebsite()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/enable_site_name', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getWebsiteDescription()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/description', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getWebsiteImage()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/website_image', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getFileUrl()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_logo', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableCompany()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/enable', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getComapnyPriceRange()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/price_range', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_name', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyTelephone()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_telephone', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyEmail()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_email', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyAddress()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_address', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyStreet()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_street', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanySocial()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_social', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyCountry()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_country', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyPostCode()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_post_code', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableName()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/name', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableSku()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/sku', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableImage()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/image', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableDescription()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/description', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableReview()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/review', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableRating()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/rating', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableAvailability()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/availability', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnablePrice()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/price', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getBreadscumbs()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/breadscumbs', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getTwitterUser()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/twitter_user', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableImageCategory()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/category/image', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableDescriptionCategory()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/category/description', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableNameCategory()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/category/name', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForWebsite()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/open_graph/for_website', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForProduct()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/open_graph/for_product', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForCategory()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/open_graph/for_category', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForWebsiteT()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/twitter_card/for_website', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForProductT()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/twitter_card/for_product', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForCategoryT()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/twitter_card/for_category', $this->scopeStore);
        return $result;
    }
}
