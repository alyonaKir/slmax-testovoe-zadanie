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
namespace Bss\SeoReport\Model\Config\Backend;

use Bss\SeoReport\Helper\Data as SeoReportHelper;

/**
 * Class Hreflang
 *
 * @package Bss\HrefLang\Model\Config\Backend
 */
class CheckAuthCode extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Bss\SeoReport\Helper\GoogleAPI
     */
    protected $googleAPI;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * CheckAuthCode constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Bss\SeoReport\Helper\GoogleAPI $googleAPI
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Bss\SeoReport\Helper\GoogleAPI $googleAPI,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        array $data = []
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->googleAPI = $googleAPI;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }
    /**
     * @inheritDoc
     *
     * @return \Magento\Config\Model\Config\Backend\Serialized $this
     * @throws \Exception
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function beforeSave()
    {
        /* @var array $value */
        $value = $this->getValue();
        $oldValue = $this->getOldValue();
        if ($value !== $oldValue) {
            //Now get User Token An Refresh Token;
            $tokenUser = $this->googleAPI->getTokenUser($value);
            $path = SeoReportHelper::SEO_REPORT_REFRESH_TOKEN;
            if (isset($tokenUser['refresh_token'])) {
                $refreshToken = $tokenUser['refresh_token'];
                $this->saveNewConfig($path, $refreshToken);
            }
            if ($value === '') {
                $this->saveNewConfig($path, '');
            }
        }
        $this->setValue($value);
        return parent::beforeSave();
    }

    /**
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string $scopeId
     * @return \Magento\Config\Model\ResourceModel\Config
     */
    protected function saveNewConfig($path, $value, $scope = 'default', $scopeId = '')
    {
        return $this->resourceConfig->saveConfig($path, $value, $scope, $scopeId);
    }
}
