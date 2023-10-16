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



namespace Mirasvit\OptimizeHtml\Processor;

use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeHtml\Model\Config;

class FontsPreloadProcessor implements OutputProcessorInterface
{
    private $config;

    private $request;

    /**
     * @var string
     */
    private $deploymentVersion;

    private $storeManager;

    public function __construct(
        Config $config,
        Request $request,
        StorageInterface $storage,
        StoreManagerInterface $storeManager
    ) {
        $this->config            = $config;
        $this->request           = $request;
        $this->deploymentVersion = $storage->load();
        $this->storeManager      = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        if ($this->request->isAjax() || strpos($content, '{"') === 0) {
            return $content;
        }

        $preloadFonts = $this->config->getFontsToPreload();
        $preload = '';

        $staticUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_STATIC);

        foreach ($preloadFonts as $font) {

            if(strpos($font, 'preconnect::') === 0) {
                $source = str_replace('preconnect::', '', $font);
                $preload .= '<link rel="preconnect" href="' . $source . '" crossorigin="anonymous"/>';
                continue;
            }

            $font = preg_replace('/(\/)?(.*static\/)?(version\d{10}\/)?/', '', $font, 1);
            $font = $staticUrl . $font;

            $preload .= '<link rel="preload" href="' . $font . '" as="font" crossorigin="anonymous"/>';
        }

        $content = preg_replace('/<\/\s*title\s*>/is', '</title>' . $preload, $content);

        return $content;
    }
}
