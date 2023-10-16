<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-optimize
 * @version   2.0.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ImageLazyLoad\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private $scopeConfig;

    private $request;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request     = $request;
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'mst_optimize/optimize_image/image_lazy_load/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getSkipNumber()
    {
        return $this->scopeConfig->getValue(
            'mst_optimize/optimize_image/image_lazy_load/skip_number',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isDebug()
    {
        return $this->request->getParam('debug') == 'lazy';
    }

    /**
     * @param string $img
     *
     * @return bool
     */
    public function isException($img)
    {
        $exceptions = (string)$this->scopeConfig->getValue(
            'mst_optimize/optimize_image/image_lazy_load/exception',
            ScopeInterface::SCOPE_STORE
        );

        $exceptions = explode(PHP_EOL, $exceptions);
        $exceptions = array_filter(array_map('trim', $exceptions));

        foreach ($exceptions as $exception) {
            if (strpos($img, $exception) !== false) {
                return true;
            }
        }

        return false;
    }
}
