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



namespace Mirasvit\OptimizeCss\Processor;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeCss\Model\Config;

class ToInlineProcessor implements OutputProcessorInterface
{
    private $config;

    private $storeManager;

    private $fs;

    public function __construct(
	  Config $config,
        Filesystem $fs,
        StoreManagerInterface $storeManager
    ) {
	  $this->config       = $config;
        $this->fs           = $fs;   
        $this->storeManager = $storeManager;
    }

    public function process($content)
    {
        if (strpos($content, '</head>') === false) {
            return $content;
        }

        // fonts may contain + symbol in the link
        preg_match_all('#(<link([^+^>]*|[^>]*font[^>]*)?>)#is', $content, $matches);

        $styles = '';

        foreach ($matches[0] as $value) {
            if (!$this->config->isToInline($value)) {
                continue;
            }

            $css = $this->getCssContent($value);
            if ($css) {
                $content = str_replace($value, "<!-- $value !!moved to inline!!-->", $content);
                $styles  .= $css;
            }
        }

        $content = str_replace('</head>', $styles . '</head>', $content);

        return $content;
    }

    /**
     * @param string $linkTag
     *
     * @return string
     */
    private function getCssContent($linkTag)
    {
        preg_match('#href=\"([^\"]*)\"#is', $linkTag, $link);
        preg_match('#media=\"([^\"]*)\"#is', $linkTag, $media);

        $mediaQuery   = '';
        $inlineStyles = '';
        $source       = $this->normalizeSource($link[1]);

        //inline only css
        if (substr($source, strlen($source) - 4, 4) !== '.css') {
            return '';
        }
        //ignore non css links
        if (strpos($linkTag, 'canonical') !== false || strpos($linkTag, 'alternate') !== false) {
            return "";
	}

	$css = $this->retrieveContentFromSource($source);

        if (is_array($media) && count($media) == 2) {
            $mediaQuery = ' media="' . $media[1] . '" ';
        }

        if ($css) {
            $inlineStyles = '<style' . $mediaQuery . '>' . $css . '</style>';
        }

        return $inlineStyles;
    }

    /**
     * @param string $source
     *
     * @return string
     */
    private function retrieveContentFromSource($source)
    {
	  $css = '';

	  $staticUrl      = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_STATIC);
        $staticRootPath = $this->fs->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath();

        $cssPath = $staticRootPath . str_replace($staticUrl, '', $source);

        if (file_exists($cssPath)) {
            $css = file_get_contents($cssPath);
        } else {
            $contextOptions = [
                "ssl" => [
                    "verify_peer"      => false,
                    "verify_peer_name" => false,
                ],
            ];

            $css = file_get_contents($source, false, stream_context_create($contextOptions));
        }

	return $css;
    }

    /**
     * @param string $source
     *
     * @return string
     */
    private function normalizeSource($source)
    {
	  if (strpos($source, '//') === 0) { // to fix issue with path without protocol
            $source = 'http:' . $source;
        }

        // fix issue with styles without domain
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        if (strpos($source, $baseUrl) === false && strpos($source, '/') === 0) {
            $source = $baseUrl . $source;
        }

	  return $source;
    }
}
