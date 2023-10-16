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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Bss\SeoReport\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    const SEO_REPORT_ENABLE = 'bss_seo_report/general/enable';
    const SEO_REPORT_API_KEY = 'bss_seo_report/general/api_key';
    const SEO_REPORT_CLIENT_ID = 'bss_seo_report/general/client_id';
    const SEO_REPORT_CLIENT_SECRET = 'bss_seo_report/general/client_secret';
    const SEO_REPORT_AUTHORIZATION_CODE = 'bss_seo_report/general/authorization_code';
    const SEO_REPORT_REFRESH_TOKEN = 'bss_seo_report/google_api/refresh_token';

    const SEO_SUITE_CANONICAL_ENABLE = 'bss_canonical/general/enable';
    const SEO_SUITE_PRODUCT_PATH = 'bss_canonical/product/path';

    const SEO_REPORT_CRAWL_MAX_URL = 100;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->date = $date;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $data
     * @return bool|false|string
     */
    public function jsonEncode(array $data)
    {
        return $this->jsonHelper->serialize($data);
    }

    /**
     * @param string $string
     * @return array|bool|float|int|mixed|string|null
     */
    public function jsonDecode(string $string)
    {
        return $this->jsonHelper->unserialize($string);
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
     * {@inheritdoc}
     */
    protected function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * Get enable module
     *
     * @param string $storeId
     * @return mixed
     */
    public function isCanonicalEnable($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::SEO_SUITE_CANONICAL_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get suffix url for product
     *
     * @param string $storeId
     * @return mixed
     */
    public function getProductSuffixUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/product_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product path
     *
     * @param string $storeId
     * @return mixed
     */
    public function getProductPath($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEO_SUITE_PRODUCT_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function getEnableModule()
    {
        return $this->scopeConfig->getValue(
            self::SEO_REPORT_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            self::SEO_REPORT_API_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAuthCode()
    {
        return $this->scopeConfig->getValue(
            self::SEO_REPORT_AUTHORIZATION_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->scopeConfig->getValue(
            self::SEO_REPORT_REFRESH_TOKEN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->scopeConfig->getValue(
            self::SEO_REPORT_CLIENT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->scopeConfig->getValue(
            self::SEO_REPORT_CLIENT_SECRET,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed|string
     */
    public function getStartDate()
    {
        $startDate = $this->scopeConfig->getValue(
            'bss_seo_report/general/start_date',
            ScopeInterface::SCOPE_STORE
        );
        if ($startDate === '' || $startDate === null || !$this->validateDate($startDate)) {
            $startDate = '2000-01-01';
        }
        return $startDate;
    }

    /**
     * @return mixed|string
     */
    public function getEndDate()
    {
        $endDateType = $this->scopeConfig->getValue(
            'bss_seo_report/general/type_end_date',
            ScopeInterface::SCOPE_STORE
        );
        if ($endDateType !== 'today') {
            $endDateCustom = $this->scopeConfig->getValue(
                'bss_seo_report/general/end_date',
                ScopeInterface::SCOPE_STORE
            );
            if ($endDateCustom === '' || $endDateCustom === null || !$this->validateDate($endDateCustom)) {
                $endDate = $this->getToDay();
            } else {
                $endDate = $endDateCustom;
            }
        } else {
            $endDate = $this->getToDay();
        }
        return $endDate;
    }

    /**
     * @return string
     */
    public function getToDay()
    {
        return $this->date->gmtDate("Y-m-d");
    }

    /**
     * @param string $date
     * @return bool
     */
    public function validateDate($date)
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
            return true;
        } else {
            return false;
        }
    }
}
