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
 * @package    Bss_HrefLang
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HrefLang\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\HrefLang\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEOSUITE_HREFLANG_ENABLE = 'bss_hreflang/hreflang/active';

    const SEOSUITE_HREFLANG_STORE = 'bss_hreflang/hreflang/store';

    const SEOSUITE_HREFLANG_PRODUCT = 'bss_hreflang/hreflang/enable_product';

    const SEOSUITE_HREFLANG_CMS = 'bss_hreflang/hreflang/enable_cms';

    const SEOSUITE_HREFLANG_CATEGORY = 'bss_hreflang/hreflang/enable_category';

    const SEOSUITE_HREFLANG_HOMEPAGE = 'bss_hreflang/hreflang/enable_homepage';

    const MAGENTO_VESION_220 = 2;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonSerializer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Json\Helper\Data $jsonSerializer
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Json\Helper\Data $jsonSerializer
    ) {
        $this->productMetadata = $productMetadata;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $context->getLogger();
        parent::__construct($context);
    }

    /**
     * Get enable hreflang
     *
     * @param string $storeId
     * @return mixed
     */
    public function getEnableHreflang($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_HREFLANG_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Store Hreflang
     *
     * @param string $storeId
     * @return mixed
     */
    public function getStoreHreflang($storeId)
    {
        $version = $this->productMetadata->getVersion();
        $versionArray = explode(".", $version);

        $data = $this->scopeConfig->getValue(
            self::SEOSUITE_HREFLANG_STORE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($data == null || $data == '') {
            return null;
        }

        $additionalData = '';
        if ($versionArray[1] < self::MAGENTO_VESION_220) {
            /* For magento version below 2.2.0, using php function. */
            $additionalData = unserialize($data);
        } else {
            try {
                $additionalData = $this->jsonSerializer->jsonDecode($data);
            } catch (\Exception $exception) {
                $this->logger->critical(__('Unable to unserialize value, details: ') . $exception->getMessage());
            }
        }

        return $additionalData;
    }

    /**
     * Get home page hreflang
     *
     * @param string $storeId
     * @return mixed
     */
    public function getHomepageHreflang($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_HREFLANG_HOMEPAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product hreflang
     *
     * @param string $storeId
     * @return mixed
     */
    public function getProductHreflang($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_HREFLANG_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get cms page hreflang
     *
     * @param string $storeId
     * @return mixed
     */
    public function getCmsHreflang($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_HREFLANG_CMS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get category hreflang
     *
     * @param string $storeId
     * @return mixed
     */
    public function getCategoryHreflang($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_HREFLANG_CATEGORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
