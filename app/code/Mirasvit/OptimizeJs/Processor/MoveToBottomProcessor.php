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

use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeJs\Model\Config;

class MoveToBottomProcessor implements OutputProcessorInterface
{
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function process($content)
    {
        if (!$this->config->isMoveJs()) {
            return $content;
        }

        if ($this->config->isMoveJsUrlException()) {
            return $content;
        }

        if (strpos($content, '</body>') === false) {
            return $content;
        }

        $js = $this->extractScriptTags($content);

        $content = str_replace('</body>', $js . '</body>', $content);

        return $content;
    }

    /**
     * Remove scripts from the content and return them as string.
     *
     * @param string $content
     * @return string
     */
    private function extractScriptTags(&$content)
    {
        $scripts     = '';
        $scriptOpen  = '<script';
        $scriptClose = '</script>';

        $scriptOpenPos = strpos($content, $scriptOpen);

        while ($scriptOpenPos !== false) {
            $scriptClosePos = strpos($content, $scriptClose, $scriptOpenPos);

            $script = substr(
                $content,
                $scriptOpenPos,
                $scriptClosePos - $scriptOpenPos + strlen($scriptClose)
            );

            $isXMagentoTemplate = strpos($script, 'text/x-magento-template') !== false;

            if ($isXMagentoTemplate || $this->config->isMoveJsException($script)) {
                $scriptOpenPos = strpos($content, $scriptOpen, $scriptClosePos);
                continue;
            }

            $scripts .= "\n" . $script;

            $expr = '#' . preg_quote($script, '#') . '\s*#';

            // remove if possible whitespace and newline characters after the script tag to avoid gaps in markup
            try {
                $content = preg_replace($expr, '', $content);
            } catch (\Exception $e) {
                $content = str_replace($script, '', $content);
            }

            $scriptOpenPos = strpos($content, $scriptOpen, $scriptOpenPos);
        }

        return $scripts;
    }
}
