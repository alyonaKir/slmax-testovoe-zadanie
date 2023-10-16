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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\XmlSiteMap\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SITEMAP_VALID_PATHS = 'bss_xmlsitemap/file/valid_paths';
    const XML_PATH_DISABLE_CMS_PAGE = 'bss_xmlsitemap/page/disable_page';
    const XML_PATH_PUBLIC_FILES_VALID_PATHS = 'general/file/public_files_valid_paths';
    const XML_PATH_MAX_LINES = 'bss_xmlsitemap/limit/max_lines';
    const XML_PATH_MAX_FILE_SIZE = 'bss_xmlsitemap/limit/max_file_size';
    const XML_PATH_DEVIDE = 'bss_xmlsitemap/limit/devide';
    const XML_PATH_CATEGORY_CHANGEFREQ = 'bss_xmlsitemap/category/changefreq';
    const XML_PATH_ADDITION_CHANGEFREQ = 'bss_xmlsitemap/addition/changefreq';
    const XML_PATH_PRODUCT_CHANGEFREQ = 'bss_xmlsitemap/product/changefreq';
    const XML_PATH_HOMEPAGE_CHANGEFREQ = 'bss_xmlsitemap/homepage/changefreq';
    const XML_PATH_HOMEPAGE_ENABLE = 'bss_xmlsitemap/homepage/enable_homepage';
    const XML_PATH_PAGE_CHANGEFREQ = 'bss_xmlsitemap/page/changefreq';
    const XML_PATH_CHECK = 'bss_xmlsitemap/product/path_check';
    const XML_PATH_CATEGORY_PRIORITY = 'bss_xmlsitemap/category/priority';
    const XML_PATH_PRODUCT_PRIORITY = 'bss_xmlsitemap/product/priority';
    const XML_PATH_HOMEPAGE_PRIORITY = 'bss_xmlsitemap/homepage/priority';
    const XML_PATH_ADDITION_PRIORITY = 'bss_xmlsitemap/addition/priority';
    const XML_PATH_HOMEPAGE_MODIFY = 'bss_xmlsitemap/homepage/enable_modify';
    const XML_PATH_PAGE_PRIORITY = 'bss_xmlsitemap/page/priority';
    const XML_PATH_SUBMISSION_ROBOTS = 'bss_xmlsitemap/search_engines/submission_robots';
    const XML_PATH_PRODUCT_IMAGES_INCLUDE = 'bss_xmlsitemap/product/image_include';
    const XML_PATH_PRODUCT_TYPE_INCLUDE = 'bss_xmlsitemap/product/disable_product';
    const XML_PATH_CATEGORY_TYPE_INCLUDE = 'bss_xmlsitemap/category/disable_category';
    const XML_PATH_PRODUCT_ID_INCLUDE = 'bss_xmlsitemap/product/disable_product_id';
    const XML_PATH_ADDITION_STRING = 'bss_xmlsitemap/addition/addition_link';
    const XML_ROOT_PATH = 'bss_xmlsitemap/generate/root_path';
    const XML_GENERAL_ENABLE = 'bss_xmlsitemap/generate/enabled';

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    private $configFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configFactory
     */
    public function __construct(
        Context $context,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configFactory
    ) {
        $this->configFactory = $configFactory;
        parent::__construct($context);
    }

    /**
     * @param int $storeId
     * @param string $path
     * @return bool|mixed
     */
    public function getConfigWithoutCache($storeId, $path)
    {
        $collection = $this->configFactory->create();
        $collection->addFieldToFilter('path', $path);

        if ((int)$storeId === 0) {
            $collection->addFieldToFilter('scope', 'default');
            $collection->addFieldToFilter('scope_id', '0');
        }
        $categoryDisable = false;
        if ($collection->getSize()) {
            $valueStoreArray = $this->getValueStore($collection, $storeId);
            $defaultValue = $valueStoreArray['default_value'];
            $categoryDisable = $valueStoreArray['store_value'];
            if ($categoryDisable === false) {
                $categoryDisable = $defaultValue;
            }
        }
        return $categoryDisable;
    }

    /**
     * @param object $collection
     * @param int $storeId
     * @return array
     */
    protected function getValueStore($collection, $storeId)
    {
        $categoryDisable = false;
        $defaultValue = '';
        foreach ($collection as $item) {
            if ((int)$storeId === 0) {
                $categoryDisable = $item->getValue();
            }
            if ((int)$item->getScopeId() === 0 && $item->getScope() == 'default') {
                $defaultValue = $item->getValue();
            }
            if ((int)$storeId !== 0 && (int)$item->getScopeId() === (int)$storeId) {
                $categoryDisable = $item->getValue();
            }
        }
        return [
            'default_value' => $defaultValue,
            'store_value' => $categoryDisable
        ];
    }

    /**
     * Get maximum sitemap.xml URLs number
     *
     * @param int $storeId
     * @return int
     */
    public function getMaximumLinesNumber($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_LINES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get maximum sitemap.xml file size in bytes
     *
     * @param int $storeId
     * @return int
     */
    public function getMaximumFileSize($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isEnableModule()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_GENERAL_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Devide sitemap.xml file size in bytes
     *
     * @param int $storeId
     * @return int
     */
    public function getDevide($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEVIDE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get disable page
     *
     * @param int $storeId
     * @return string
     */
    public function getDisablePage($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_DISABLE_CMS_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get category change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryChangefreq($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getProductChangefreq($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getHomepageChangefreq($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_HOMEPAGE_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getHomepageEnable($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_HOMEPAGE_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get addition link change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getAdditionChangefreq($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDITION_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get addition link change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getAdditionString($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDITION_STRING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $storeId
     * @return bool
     */
    public function isHomepageModify($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_HOMEPAGE_MODIFY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get page change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getPageChangefreq($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PAGE_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get category priority
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product priority
     *
     * @param int $storeId
     * @return string
     */
    public function getProductPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $storeId
     * @return bool
     */
    public function isCheckPath($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CHECK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Addition priority
     *
     * @param int $storeId
     * @return string
     */
    public function getAdditionPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDITION_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get root path
     *
     * @return mixed|string
     */
    public function getRootPath()
    {
        $result = $this->scopeConfig->getValue(self::XML_ROOT_PATH, ScopeInterface::SCOPE_STORE);
        $result = trim($result, "/");
        if ($result) {
            $result = '/' . $result . '/';
        } else {
            $result = '/';
        }
        return $result;
    }

    /**
     * Get product priority
     *
     * @param int $storeId
     * @return string
     */
    public function getHomepagePriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_HOMEPAGE_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get page priority
     *
     * @param int $storeId
     * @return string
     */
    public function getPagePriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PAGE_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get enable Submission to Robots.txt
     *
     * @param int $storeId
     * @return int
     */
    public function getEnableSubmissionRobots($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUBMISSION_ROBOTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product image include policy
     *
     * @param int $storeId
     * @return string
     */
    public function getProductImageIncludePolicy($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_IMAGES_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product type include policy
     *
     * @param int $storeId
     * @return string
     */
    public function getProductTypeInclude($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_TYPE_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Category ID include policy
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryIdInclude($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_TYPE_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Product ID include policy
     *
     * @param int $storeId
     * @return string
     */
    public function getProductIdInclude($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_ID_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getStyleDescription()
    {
        return (string)$this->scopeConfig->getValue(
            'bss_xmlsitemap/generate/style_description',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getStyleTitle()
    {
        return (string)$this->scopeConfig->getValue(
            'bss_xmlsitemap/generate/style_title',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get list valid paths for generate a sitemap XML file
     *
     * @return string[]
     */
    public function getValidPaths()
    {
        return array_merge(
            $this->scopeConfig->getValue(self::XML_PATH_SITEMAP_VALID_PATHS, ScopeInterface::SCOPE_STORE),
            $this->scopeConfig->getValue(self::XML_PATH_PUBLIC_FILES_VALID_PATHS, ScopeInterface::SCOPE_STORE)
        );
    }
}
