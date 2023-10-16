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
 * @package    Bss_SeoToolbar
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoToolbar\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Bss\SeoToolbar\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    public $postDataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Api\StoreCookieManagerInterface
     */
    public $storeCookieManager;

    const SEO_TOOLBAR_ENABLE = 'bss_seo_toolbar/general/enable';
    const SEO_TOOLBAR_PASSWORD = 'bss_seo_toolbar/general/hash_key';
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->storeCookieManager = $storeCookieManager;
        $this->postDataHelper = $postDataHelper;
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
     * @return bool
     */
    public function isEnableModule()
    {
        return $this->scopeConfig->isSetFlag(
            self::SEO_TOOLBAR_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param int $number
     * @return string
     */
    public function getRandomString($number = 6) : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $number; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @return string
     */
    public function getPassword() : string
    {
        $password = $this->scopeConfig->getValue(
            self::SEO_TOOLBAR_PASSWORD,
            ScopeInterface::SCOPE_STORE
        );
        if ($password === '' ||  $password === null) {
            $password = ']8GqBv2:-D';
        }
        return $password;
    }

    /**
     * @param string $dataInput
     * @return bool|mixed
     */
    public function decodeData(string $dataInput)
    {
        if ($dataInput) {
            $dataInputArray = explode('.', $dataInput);
            if (!isset($dataInputArray[1])) {
                return false;
            }
            $dataBase64 = $dataInputArray[0];
            $checkSum = $dataInputArray[1];
            $securityCode = $this->getPassword();
            try {
                $dataEncodeWithKey = $dataBase64 . $securityCode;
                $checkSumForCheck = md5($dataEncodeWithKey);
                if ($checkSumForCheck === $checkSum) {
                    $dataJsonDecode = $this->base64urlDecode($dataBase64);
                    $dataArrayAfter = $this->jsonDecode($dataJsonDecode, true);
                    ksort($dataArrayAfter);
                    return $dataArrayAfter;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param array $dataObject
     * @return string
     */
    public function encodeData(array $dataObject)
    {
        ksort($dataObject);
        $dataJsonEncode = $this->jsonEncode($dataObject);
        $dataBase64 = $this->base64urlEncode($dataJsonEncode);
        $securityCode = $this->getPassword();
        $dataEncodeWithKey = $dataBase64 . $securityCode;
        $checkSum = md5($dataEncodeWithKey);
        $dataJsonFinal = $this->jsonEncode($dataObject);
        $dataBase64Final = $this->base64urlEncode($dataJsonFinal) . '.' . $checkSum;
        return $dataBase64Final;
    }

    /**
     * @param string $data
     * @return string
     */
    public function base64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param string $data
     * @return bool|string
     */
    public function base64urlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
