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




namespace Mirasvit\OptimizeJs\Processor;


use Magento\Framework\View\Layout;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeJs\Block\Frontend\YoutubeBlock;
use Mirasvit\OptimizeJs\Model\Config;

class IframeProcessor implements OutputProcessorInterface
{
    private static $counter = 0;

    private $config;

    private $layout;

    public function __construct(Config $config, Layout $layout)
    {
        $this->config  = $config;
        $this->layout  = $layout;
    }

    /**
     * @inheritDoc
     */
    public function process($content)
    {
        if (!$this->config->isLazyIframeEnabled() && !$this->config->isLazyYoutubeEnabled()) {
            return $content;
        }

        $content = preg_replace_callback(
            '/(<\s*iframe[^>]+)(src\s*=\s*["\']([^"\']+)[\'"])([^>]{0,}>\s*<\/iframe>)/is',
            [$this, 'iframeReplaceCallback'],
            $content
        );

        if (
            self::$counter > 0
            && $this->config->isLazyIframeEnabled()
            && strpos($content, 'window.lazySizesConfig') === false
            && !$this->config->isLazyDebug()
        ) {
            $script = '
            <script>
                 window.lazySizesConfig = window.lazySizesConfig || {};
                 window.lazySizesConfig.lazyClass = "mst-lazy";
                 lazySizesConfig.srcAttr = "data-mst-lazy-src";
                 lazySizesConfig.srcsetAttr = "data-mst-lazy-srcset";
                 lazySizesConfig.loadMode = 1;

                 require(["Mirasvit_ImageLazyLoad/js/lazysizes"], function() {});
            </script>';

            $content .= $script;
        }

        return $content;
    }

    /**
     * @param array $match
     * @return mixed|string
     */
    private function iframeReplaceCallback(array $match)
    {
        self::$counter++;

        // Youtube iframe
        if (strpos($match[3], 'youtube') !== false && $this->config->isLazyYoutubeEnabled()) {
            $ytUrl = $match[3];
            $title = preg_match('/title\s*=\s*["\']([^"\']+)[\'"]/', $match[4]);
            $title = $title && !is_int($title) && is_array($title) ? $title[1] : '';
            $srcDoc = $this->prepareSrcdoc($ytUrl, $title);

            $replacement = $match[1] . $match[2] . ' srcdoc = "' . $srcDoc . '" ' . $match[4];

            return $replacement;
        }

        if ($this->config->isLazyIframeException($match[0])) {
            return $match[0];
        }

        // Any iframe
        if ($this->config->isLazyIframeEnabled()) {
            $url      = substr($match[2], 5, strlen($match[2]) - 6);
            $replaced = $match[1] . ' data-mst-lazy-src="' . $url . '"' . $match[4];
            $replaced = preg_replace('/class\s*=\s*"/i', 'class="mst-lazy ', $replaced);

            if (strpos($replaced, 'class=') === false) {
                $replaced = str_replace('>', ' class="mst-lazy">', $replaced);
            }

            if ($this->config->isLazyDebug()) {
                $replaced = str_replace('>', ' style="background:yellow">', $replaced);
            }

            $replaced = preg_replace('/><\/iframe>/is', ' loading="lazy"></iframe>', $replaced);
            $replaced .= '<noscript>' . $match[0] . '</noscript>';

            return $replaced;
        }

        return $match[0];
    }

    /**
     * @param string $ytUrl
     * @param string $alt
     * @return string
     */
    private function prepareSrcdoc($ytUrl, $alt = '')
    {
        $previewImage = $ytUrl;
        $previewImage = str_replace('www.youtube-nocookie', 'www.youtube', $previewImage);
        $previewImage = str_replace('www.youtube', 'img.youtube', $previewImage);
        $previewImage = str_replace('/embed/', '/vi/', $previewImage);

        if (preg_match('/\?.*/', $previewImage)) {
            $previewImage = preg_replace('/\?.*/', '/hqdefault.jpg', $previewImage);
        } else {
            $previewImage .= '/hqdefault.jpg';
        }

        /**
         * @var YoutubeBlock $block
         */
        $block = $this->layout->createBlock(
            YoutubeBlock::class,
            'mst_iframe_srcdoc-' . self::$counter,
            ['data' => [
                    'src_link' => $ytUrl,
                    'img_link' => $previewImage,
                    'img_alt' => $alt
                ]]
        )->setTemplate('Mirasvit_OptimizeJs::youtubeSrcdoc.phtml');

        return $block->toHtml();
    }
}
