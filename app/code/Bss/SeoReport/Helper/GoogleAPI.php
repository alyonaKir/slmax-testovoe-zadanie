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

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class StringProcess
 * @package Bss\SeoReport\Helper
 */
class GoogleAPI
{
    const CLIENT_ID = '349600568090-lkd6thrghmt050h47ajdedtu5898ejvd.apps.googleusercontent.com';
    const CLIENT_SECRET = 'kuOTFzUgkL5A0rRS-vGAKLcf';
    const REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';
    const URL_OAUTH_2 = 'https://www.googleapis.com/oauth2/v4/token';
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var Json
     */
    private $jsonHelper;

    /**
     * GoogleAPI constructor.
     * @param Json $jsonHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Json $jsonHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
    }

    /**
     * @param $code
     * @param $clientId
     * @param $clientSecret
     * @param $redirectUri
     * @return bool|mixed|string
     */
    public function getTokenUser($code)
    {
        $redirectUri = self::REDIRECT_URI;
        $redirectUri = urlencode($redirectUri);
        $code = urlencode($code);
        $userField = 'code=' . $code . '&client_id=' . self::CLIENT_ID . '&client_secret=' . self::CLIENT_SECRET .
            '&redirect_uri=' . $redirectUri . '&grant_type=authorization_code';
        $result = $this->handleTokenUser($userField);
        return $result;
    }

    /**
     * @param $refreshCode
     * @return bool|mixed|string
     */
    public function refreshTokenUser($refreshCode)
    {
        $refreshCode = urlencode($refreshCode);
        $userField = 'refresh_token=' . $refreshCode . '&client_id=' . self::CLIENT_ID . '&client_secret=' . self::CLIENT_SECRET .
            '&grant_type=refresh_token';
        $result = $this->handleTokenUser($userField);
        return $result;
    }
    /**
     * @param $postField
     * @return bool|mixed|string
     */
    public function handleTokenUser($postField)
    {
        $response = '';
        try {
            $url = self::URL_OAUTH_2;
            $timeout = 20;
            $url = str_replace("&amp;", "&", urldecode(trim($url)));
            $ch = curl_init();
            $postHeader = [
                'Content-Type: application/x-www-form-urlencoded',
                'Host: www.googleapis.com'
            ];
            curl_setopt(
                $ch,
                CURLOPT_USERAGENT,
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1"
            );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $postHeader);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $response = curl_exec($ch);
            curl_close($ch);
            $response = $this->jsonHelper->unserialize($response, true);
            return $response;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $response;
    }

    /**
     * @param $siteUrl
     * @param $accessToken
     * @param $tokenType
     * @param $dataJson
     * @return bool|mixed|string
     */
    public function getSiteInfo($siteUrl, $accessToken, $tokenType, $dataJson)
    {
        $siteUrl = urlencode($siteUrl);
        $url = 'https://www.googleapis.com/webmasters/v3/sites/' . $siteUrl . '/searchAnalytics/query';

        $response = '';
        try {
            $timeout = 20;
            $ch = curl_init();
            $authorization = 'Authorization: ' . $tokenType . ' ' . $accessToken;
            $postHeader = [
                'Content-Type: application/json',
                'Accept: application/json',
                $authorization
            ];

            curl_setopt(
                $ch,
                CURLOPT_USERAGENT,
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1"
            );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $postHeader);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $response = curl_exec($ch);
            curl_close($ch);
            $response = $this->jsonHelper->unserialize($response, true);
            return $response;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $response;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $dimensionsObject
     * @param array $filterObject
     * @param int $limit
     * @param int $offset
     * @return false|string
     */
    public function getGoogleConsoleByKeyword(
        $startDate,
        $endDate,
        $dimensionsObject,
        $filterObject = [],
        $limit = 10,
        $offset = 0
    ) {
        $objectReturn = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        $objectReturn['dimensions'] = [];
        $objectReturn['dimensions']  = array_merge($objectReturn['dimensions'], $dimensionsObject);
        if (!empty($filterObject)) {
            $objectReturn['dimensionFilterGroups'][] = [
                "groupType" => "and",
                "filters" => $filterObject
            ];
        }
        $objectReturn['rowLimit'] = $limit;
        $objectReturn['startRow'] = $offset;

        $dataEncode = $this->jsonHelper->serialize($objectReturn);
        return $dataEncode;
    }
}
