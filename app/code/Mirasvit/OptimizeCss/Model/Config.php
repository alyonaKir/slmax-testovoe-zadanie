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



namespace Mirasvit\OptimizeCss\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isMinifyCss()
    {
        return (bool)$this->scopeConfig->getValue('dev/css/minify_files');
    }

    public function isDeferGoogleFont()
    {
        return (bool)$this->scopeConfig->getValue('mst_optimize/optimize_css/defer_google_font');
    }

    public function isMoveCss()
    {
        return $this->scopeConfig->getValue(
            'mst_optimize/optimize_css/move_css',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $css
     *
     * @return bool
     */
    public function isMoveException($css)
    {
	  $predefinedExceptions = [
	      'canonical',
	      'alternate',
	      'image/x-icon'
	  ];

        $exceptions = $this->getPatternsByPath('mst_optimize/optimize_css/move_css_exception');
        $toInline   = $this->getPatternsByPath('mst_optimize/optimize_css/inline_css');

        $exceptions = array_unique(array_merge($predefinedExceptions, $exceptions, $toInline));

        foreach ($exceptions as $exception) {
            if (strpos($css, $exception) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $css
     *
     * @return bool
     */
    public function isPredefinedException($css)
    {
        $predefinedExceptions = [
            'canonical',
            'alternate',
            'image/x-icon'
        ];

        foreach ($predefinedExceptions as $exception) {
            if (strpos($css, $exception) !== false) {
                return true;
            }
        }

        return false;
    }

    public function isPreloadExceptions()
    {
        return $this->scopeConfig->getValue(
            'mst_optimize/optimize_css/is_preload_exception',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $linkTag
     *
     * @return bool
     */
    public function isToInline($linkTag)
    {
        foreach ($this->getPatternsByPath('mst_optimize/optimize_css/inline_css') as $inline) {
            $pattern = str_replace('inline::', '', $inline);

            if(strpos($linkTag, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $path
     * @return array
     */
    private function getPatternsByPath($path)
    {
        $patterns = (string)$this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );

        $patterns = explode(PHP_EOL, $patterns);
        $patterns = array_filter(array_map('trim', $patterns));

        return $patterns;
    }
}
