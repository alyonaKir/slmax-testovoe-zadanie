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



namespace Mirasvit\OptimizeJs\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private $scopeConfig;

    /**
     * @var RequestInterface
     */
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
        return $this->scopeConfig->getValue('mst_optimize/optimize_js/enabled');
    }

    /**
     * @return array
     */
    public function getJsBundleExceptions()
    {
        $patterns = (string)$this->scopeConfig->getValue('mst_optimize/optimize_js/bundle_js_exception');

        $patterns = explode(PHP_EOL, $patterns);
        $patterns = array_filter(array_map('trim', $patterns));

        return $patterns;
    }

    public function isJsBundleException()
    {
        $patterns = $this->getJsBundleExceptions();

        foreach ($patterns as $pattern) {
            if (
                $this->request->getFullActionName() === $pattern
                || strpos($this->request->getRequestUri(), $pattern) !== false
            ) {
                return true;
            }
        }

        return false;
    }

    public function isMinifyJs()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue('dev/js/minify_files');
    }

    /**
     * @return array
     */
    public function getMinifyJsExceptions()
    {
        $patterns = (string)$this->scopeConfig->getValue('mst_optimize/optimize_js/minify_js_exception');

        $patterns = explode(PHP_EOL, $patterns);
        $patterns = array_filter(array_map('trim', $patterns));

        return $patterns;
    }

    public function isMoveJs()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue('mst_optimize/optimize_js/move_js');
    }

    public function isMoveJsUrlException()
    {
        $patterns = (string)$this->scopeConfig->getValue(
            'mst_optimize/optimize_js/move_js_url_exception',
            ScopeInterface::SCOPE_STORE
        );

        $patterns = explode(PHP_EOL, $patterns);
        $patterns = array_filter(array_map('trim', $patterns));

        foreach ($patterns as $pattern) {
            if (strpos($this->request->getRequestUri(), $pattern) !== false) {
                return true;
            }

            if ($this->request->getFullActionName() === $pattern) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $js
     *
     * @return bool
     */
    public function isMoveJsException($js)
    {
        $patterns = (string)$this->scopeConfig->getValue(
            'mst_optimize/optimize_js/move_js_url_exception',
            ScopeInterface::SCOPE_STORE
        );

        $patterns = explode(PHP_EOL, $patterns);
        $patterns = array_filter(array_map('trim', $patterns));

        foreach ($patterns as $pattern) {
            if (strpos($js, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isLazyIframeEnabled()
    {
        return $this->scopeConfig->getValue(
            'mst_optimize/optimize_js/iframe_lazy',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isLazyYoutubeEnabled()
    {
        return $this->scopeConfig->getValue(
            'mst_optimize/optimize_js/youtube_lazy',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isLazyDebug()
    {
        return $this->request->getParam('debug') == 'lazy';
    }

    /**
     * @param string $iframe
     * @return bool
     */
    public function isLazyIframeException($iframe)
    {
        $patterns = $this->scopeConfig->getValue(
            'mst_optimize/optimize_js/iframe_lazy_exception',
            ScopeInterface::SCOPE_STORE
        );
        
        if (!$patterns) {
            return false;
        }

        $patterns = array_filter(explode(PHP_EOL, $patterns));

        foreach ($patterns as $pattern) {
            if (!trim($pattern)) {
                continue;
            }

            if (strpos($iframe, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
