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



namespace Mirasvit\ImageLazyLoad\Processor;

use Mirasvit\ImageLazyLoad\Model\Config;
use Mirasvit\ImageLazyLoad\Service\ImageService;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;

class LazyLoadProcessor implements OutputProcessorInterface
{
    private $config;

    private $imageService;

    private $imgCounter  = 0;

    private $webpCounter = 0;

    private $noscriptStyleInserted = false;

    public function __construct(
        ImageService $imageService,
        Config $config
    ) {
        $this->config       = $config;
        $this->imageService = $imageService;
    }

    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        if (!$this->config->isEnabled()) {
            return $content;
        }

        $content = preg_replace_callback(
            '/(<\s*img[^>]*)(\ssrc\s*=\s*["\'][^"\'<]+[\'"])([^>]{0,}>)/is',
            [$this, 'replaceImgCallback'],
            $content
        );

        $content = preg_replace_callback(
            '/(<\s*img[^>]*)(\ssrcset\s*=\s*["\'][^"\'<]+[\'"])([^>]{0,}>)/is',
            [$this, 'replaceResponsiveImgCallback'],
            $content
        );

        $content = preg_replace_callback(
            '/(<\s*source[^>]+)(srcset\s*=\s*"[^"]+")([^>]{0,}>)/is',
            [$this, 'replaceSourceCallback'],
            $content
        );

        $content = $this->updatePictureTags($content);

        if ($this->imgCounter > 0) {
            $content = $this->appendLazyLoadLib($content);
        }

        return $content;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    private function replaceImgCallback(array $match)
    {
        if (++$this->imgCounter <= $this->config->getSkipNumber()) {
            return $match[0];
        }

        if ($this->config->isException($match[0])) {
            return $match[0];
        }

        $url = substr($match[2], 6, strlen($match[2]) - 7);
        $px  = $this->imageService->getPlaceholder($url, $this->config->isDebug());

        $replaced = $match[1] . ' src="' . $px . '" data-mst-lazy-src="' . $url . '"' . $match[3];
        $replaced = preg_replace('/class\s*=\s*"/i', ' class="mst-lazy ', $replaced);

        if (strpos($replaced, 'class=') === false) {
            $replaced = preg_replace('/\/?>/', ' class="mst-lazy">', $replaced);
        }

        $replaced .= '<noscript>' . $match[0] . '</noscript>';

        return $replaced;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    private function replaceResponsiveImgCallback(array $match)
    {
        if ($this->imgCounter <= $this->config->getSkipNumber()) {
            return $match[0];
        }

        if ($this->config->isException($match[0])) {
            return $match[0];
        }

        $srcset   = substr($match[2], 9, strlen($match[2]) - 10);
        $replaced = $match[1] . ' data-mst-lazy-srcset="' . $srcset . '"' . $match[3];

        // remove lazyload for src
        $replaced = preg_replace('/ data-mst-lazy-src=["\'][^"\']+[\'"]/', '', $replaced);
        $replaced = preg_replace('/class\s*=\s*"/i', ' class="mst-lazy ', $replaced);

        if (strpos($replaced, 'class=') === false) {
            $replaced = preg_replace('/\/?>/', ' class="mst-lazy">', $replaced);
        }

        $replaced .= '<noscript>' . $match[0] . '</noscript>';

        return $replaced;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    private function replaceSourceCallback(array $match)
    {
        if (++$this->webpCounter <= $this->config->getSkipNumber()) {
            return $match[0];
        }

        if ($this->config->isException($match[0])) {
            return $match[0];
        }

        $url = substr($match[2], 8, strlen($match[2]) - 9);
        $px  = $this->imageService->getPlaceholder($url, $this->config->isDebug());

        $replaced = $match[1] . 'srcset="' . $px . '" data-mst-lazy-srcset="' . $url . '"' . $match[3];
        $replaced .= '<noscript>' . $match[0] . '</noscript>';

        return $replaced;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function appendLazyLoadLib($content)
    {
        if ($this->config->isDebug()) {
            return $content;
        }

        $script = '
            <script>
                 window.lazySizesConfig = window.lazySizesConfig || {};
                 window.lazySizesConfig.lazyClass = "mst-lazy";
                 lazySizesConfig.srcAttr = "data-mst-lazy-src";
                 lazySizesConfig.srcsetAttr = "data-mst-lazy-srcset";
                 lazySizesConfig.loadMode = 1;

                 require(["Mirasvit_ImageLazyLoad/js/lazysizes"], function() {});
            </script>';

        return $content . $script;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function movePictureNoscript($content)
    {
        $pictureOpenTag   = '<picture>';
        $pictureCloseTag  = '</picture>';
        $noscriptOpenTag  = '<noscript>';
        $noscriptCloseTag = '</noscript>';

        $pictureOpenPos = strpos($content, $pictureOpenTag);

        while ($pictureOpenPos !== false) {
            $pictureClosePos = strpos($content, $pictureCloseTag, $pictureOpenPos);

            $pictureTag = substr(
                $content,
                $pictureOpenPos,
                $pictureClosePos - $pictureOpenPos + strlen($pictureCloseTag)
            );

            $updatedPictureTag = $pictureTag;
            $noscriptContent   = '';

            $noscriptOpenPos = strpos($updatedPictureTag, $noscriptOpenTag);

            while ($noscriptOpenPos !== false) {
                $noscriptClosePos = strpos($updatedPictureTag, $noscriptCloseTag, $noscriptOpenPos);

                $noscript = substr(
                    $updatedPictureTag,
                    $noscriptOpenPos,
                    $noscriptClosePos - $noscriptOpenPos + strlen($noscriptCloseTag)
                );

                $noscriptContent .= substr(
                        $noscript,
                        strlen($noscriptOpenTag),
                        -strlen($noscriptCloseTag)
                    );

                $updatedPictureTag = str_replace($noscript, '', $updatedPictureTag);

                $noscriptOpenPos = strpos($updatedPictureTag, $noscriptOpenTag, $noscriptOpenPos);
            }

            if(strlen($noscriptContent)) {
                $noscriptStyle = $this->noscriptStyleInserted ? '' : '<style>.mst-lazy {display:none;}</style>';
                $this->noscriptStyleInserted = true;

                $updatedPictureTag = $updatedPictureTag
                    . $noscriptOpenTag
                    . $pictureOpenTag
                    . $noscriptContent
                    . $pictureCloseTag
                    . $noscriptStyle
                    . $noscriptCloseTag;

                $content = str_replace($pictureTag, $updatedPictureTag, $content);
            }

            $pictureOpenPos = strpos($content, $pictureOpenTag, $pictureOpenPos + strlen($updatedPictureTag));
        }

        return $content;
    }

    /**
     * @param $content
     *
     * @return string
     */
    private function updatePictureTags($content)
    {
        $splitted = explode('</picture>', $content);

        foreach ($splitted as $idx => $c) {
            if ($idx < count($splitted) - 1) {
                $c .= '</picture>';
            }

            $splitted[$idx] = $this->movePictureNoscript($c);
        }

        return implode('', $splitted);
    }
}
