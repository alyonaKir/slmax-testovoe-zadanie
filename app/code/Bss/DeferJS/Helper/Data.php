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
 * @package    Bss_DeferJS
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\DeferJS\Helper;

use \Magento\Store\Model\ScopeInterface;

class Data
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    public $productMetadata;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->productMetadata = $productMetadata;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $request
     * @return bool
     */
    public function isEnabled($request)
    {
        $active =  $this->scopeConfig->getValue('deferjs/general/active', ScopeInterface::SCOPE_STORE);
        if ($active != 1) {
            return false;
        }

        //check home page
        $active =  $this->scopeConfig->getValue('deferjs/general/home_page', ScopeInterface::SCOPE_STORE);
        if ($active == 1 && $request->getFullActionName() == 'cms_index_index') {
            return false;
        }

        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        //check controller
        if ($this->regexMatchSimple(
            $this->scopeConfig->getValue('deferjs/general/controller', ScopeInterface::SCOPE_STORE),
            "{$module}_{$controller}_{$action}",
            1
        )
            ) {
            return false;
        }
        
        //check path
        if ($this->regexMatchSimple(
            $this->scopeConfig->getValue('deferjs/general/path', ScopeInterface::SCOPE_STORE),
            $request->getRequestUri(),
            2
        )
            ) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function inBody()
    {
        $active =  $this->scopeConfig->getValue('deferjs/general/in_body', ScopeInterface::SCOPE_STORE);
        if ($active != 1) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isDeferIframe()
    {
        $active =  $this->scopeConfig->getValue('deferjs/general/iframe', ScopeInterface::SCOPE_STORE);
        if ($active != 1) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function showControllersPath()
    {
        $active =  $this->scopeConfig->getValue('deferjs/general/show_path', ScopeInterface::SCOPE_STORE);
        if ($active != 1) {
            return false;
        }

        return true;
    }

    /**
     * @param $regex
     * @param $matchTerm
     * @param $type
     * @return bool
     */
    public function regexMatchSimple($regex, $matchTerm, $type)
    {

        if (!$regex) {
            return false;
        }
        $rules = $this->getRuleByVersion($regex);
        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $rule) {
            $regex = trim($rule['defer'], '#');
            if ($regex == '') {
                continue;
            }
            if ($type == 1) {
                $regexs = explode('_', $regex);
                $count = $this->countRegexs($regexs);
                switch ($count) {
                    case 1:
                        $regex = $regex.'_index_index';
                        break;
                    case 2:
                        $regex = $regex.'_index';
                        break;
                    default:
                        break;
                }
            }

            $regexp = '#' . $regex . '#';
            if (preg_match($regexp, $matchTerm)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $regex
     * @return mixed
     */
    protected function getRuleByVersion($regex)
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.2.0') >= 0) {
            $rules = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class)->unserialize($regex);
        } else {
            $rules = unserialize($regex);
        }
        return $rules;
    }

    /**
     * @param $regexs
     * @return int|void
     */
    protected function countRegexs($regexs)
    {
        return count($regexs);
    }
}
