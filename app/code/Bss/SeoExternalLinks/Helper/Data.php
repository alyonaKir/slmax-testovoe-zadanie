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
 * @package    Bss_SeoExternalLinks
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoExternalLinks\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const AREA_CODE = \Magento\Framework\App\Area::AREA_ADMINHTML;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    public $postDataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->postDataHelper = $postDataHelper;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return mixed
     */
    public function getModelEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'bss_seo_external_links/general/enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getDomainExcluded()
    {
        return $this->scopeConfig->getValue(
            'bss_seo_external_links/general/domain_excluded',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $html
     * @return string|string[]|null
     */
    public function addNofollow($html)
    {
        $dataReturn = preg_replace_callback(
            "#(<a[^>]+?)>#is",
            function ($mach) {
                $allowedDomain = $this->getExcludeDomain();
                $statusSkip = false;
                if (!empty($allowedDomain) && (strpos($mach[1], 'rel=') === false)) {
                    foreach ($allowedDomain as $domain) {
                        if (strpos($mach[1], $domain) !== false) {
                            $statusSkip = true;
                        }
                    }
                    if (!$this->getFirstLinkFromString($mach[1])) {
                        $statusSkip = true;
                    }
                } else {
                    $statusSkip = true;
                }
                if ($statusSkip) {
                    return $mach[0];
                } else {
                    return $mach[1] . ' rel="nofollow">';
                }
            },
            $html
        );
        return $dataReturn;
    }

    /**
     * @param string $string
     * @return bool|mixed
     */
    public function getFirstLinkFromString($string)
    {
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $string, $match);
        if (isset($match[0])) {
            return $match[0];
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getExcludeDomain()
    {
        $excludedDomain = $this->getDomainExcluded();
        $excludedDomainArray = [];
        if ($excludedDomain) {
            $excludedDomainArray = explode(',', $excludedDomain);
        }

        $siteDomain = [];
        $storeManager = $this->getStoreManager();
        //Get all Domain of Config
        $stores = $storeManager->getStores(false);
        foreach ($stores as $store) {
            if (!(int)$store->getIsActive()) {
                continue;
            }
            $baseUrl = $store->getBaseUrl();
            $domainBaseUrl = $this->getHostByUrl($baseUrl);
            if ($domainBaseUrl && !in_array($domainBaseUrl, $siteDomain)) {
                $siteDomain[] = $domainBaseUrl;
            }
        }
        //Merger Two Array
        $arrayAllowed = array_merge($siteDomain, $excludedDomainArray);
        return $arrayAllowed;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getHostByUrl($url)
    {
        //Make Url Array
        $urlObject = parse_url($url);
        if (isset($urlObject['host'])) {
            return $urlObject['host'];
        } else {
            return '';
        }
    }
}
