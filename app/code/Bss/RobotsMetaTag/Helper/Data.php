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
 * @package    Bss_RobotsMetaTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RobotsMetaTag\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\RobotsMetaTag\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEOSUITE_ROBOTS_ENABLE = 'bss_robots/robots/active';

    const SEOSUITE_ROBOTS_URL = 'bss_robots/robots/url';

    const SEOSUITE_ROBOTS_NOINDEX = 'bss_robots/robots/noindex_for';

    const MAGENTO_VESION_220 = 2;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param JsonHelper $serializer
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->productMetadata = $productMetadata;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * Get enable robots
     *
     * @param string $storeId
     * @return mixed
     */
    public function getEnableRobots($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_ROBOTS_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get url robots
     *
     * @param string $storeId
     * @return mixed
     */
    public function getUrlRobots($storeId)
    {
        $data = $this->scopeConfig->getValue(
            self::SEOSUITE_ROBOTS_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($data == null || $data == '') {
            return [];
        }
        $additionalData = $this->serializer->unserialize($data);
        return $additionalData;
    }

    /**
     * Get noindex
     *
     * @param string $storeId
     * @return mixed
     */
    public function getNoindexRobots($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEOSUITE_ROBOTS_NOINDEX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check url path
     *
     * @param string $path
     * @return bool
     */
    public function checkPathUrl($path)
    {
        $pathNew = 'new' . $path . 'new';
        $path = rtrim($path, '/');
        $path = ltrim($path, '/');
        $pathArray = explode('/', $pathNew);
        if (isset($pathArray[1])) {
            if ($pathArray[1] === $path) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Check url
     *
     * @param string $url
     * @param string $path
     * @return bool
     */
    public function checkUrl($url, $path)
    {
        if ($this->checkPathUrl($path)) {
            if (strpos($url, $path) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            $path = $path . '.html';
            if (strpos($url, $path) === false) {
                return false;
            } else {
                return true;
            }
        }
    }
}
