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


declare(strict_types=1);

namespace Mirasvit\OptimizeImage\Processor;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Model\ConfigProvider;
use Mirasvit\OptimizeImage\Repository\FileRepository;
use Mirasvit\OptimizeImage\Service\FileListSynchronizationService;
use Mirasvit\OptimizeImage\Service\ResponsiveImageService;

class WebpProcessor implements OutputProcessorInterface
{
    private $configProvider;

    private $mediaUrl;

    private $mediaDir;

    private $baseUrl;

    private $baseDir;

    private $fileRepository;

    private $syncService;

    public function __construct(
        ConfigProvider $configProvider,
        FileRepository $fileRepository,
        FileListSynchronizationService $syncService,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->fileRepository = $fileRepository;
        $this->syncService    = $syncService;
        $this->mediaUrl       = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $this->baseUrl        = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $this->mediaDir       = $filesystem->getDirectoryread(DirectoryList::MEDIA);
        $this->baseDir        = $filesystem->getDirectoryread(DirectoryList::ROOT);
    }

    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        if (!$this->configProvider->isWebpEnabled()) {
            return $content;
        }

        // Case 1: images with js code after src
        $content = preg_replace_callback(
            '/(<\s*img[^>]+)src\s*=\s*["\']([^"\'\?]+)(\?[^"\']*)?[\'"]([^>]{0,}(?:=>)+[^>]{0,}>(\s*<\/picture>)?)/is',
            [$this, 'replaceCallback'],
            $content
        );

        // Case 2: images with possible js code before src
        $content = preg_replace_callback(
            '/(<\s*img[^>]+(?:=>[^>]+)*)src\s*=\s*["\']([^"\'\?]+)(\?[^"\']*)?[\'"]([^>]{0,}>(\s*<\/picture>)?)/is',
            [$this, 'replaceCallback'],
            $content
        );

        $content = $this->appendSwatcherFixScript($content);

        return $content;
    }

    private function replaceCallback(array $match): string
    {
        if ($this->configProvider->isWebpException($match[0]) || isset($match[5])) {
            return $match[0];
        }

        $url = $match[2];

        if (strpos($url, $this->baseUrl) === false) {
            return $match[0];
        }

        // already processed by first regex
        if (strpos($url, ConfigProvider::WEBP_SUFFIX) !== false) {
            return $match[0];
        }

        $absolutePath = $this->configProvider->retrieveImageAbsPath($url);

        if (!$absolutePath) {
            return $match[0];
        }

        $path         = $this->configProvider->getRelativePath($absolutePath);
        $relativePath = $this->configProvider->getRelativePath($absolutePath);
        $image        = $this->fileRepository->getByRelativePath($relativePath);

        if (!$image) {
            $image = $this->fileRepository->getByOptimizedPath($relativePath);
        }

        if (!$image || !$image->getId() || !$image->getWebpPath()) {
            return $match[0];
        }

        if (!$this->configProvider->isFileExist($image->getWebpPath())) {
            $image->setWebpPath(null);

            $this->fileRepository->save($image);

            return $match[0];
        }

        $classes = '';

        if (preg_match('/class\s*=\s*"[^"]*"/i', $match[0], $found)) {
            $classes = ' data-mst-' . $found[0];
        }

        $defaultSource    = $this->getDefaultSource($image, $path, $classes, $match[3]);
        $responsiveSource = $this->getResponsiveSource($image, $path, $classes);

        return '<picture>' . $responsiveSource . $defaultSource . $match[0] . '</picture>';
    }

    private function getResponsiveSource(FileInterface $image, string $path, string $classes): string
    {
        $source = '';

        $ext           = $this->configProvider->getFileExtension($path);
        $resizedSuffix = '.' . ResponsiveImageService::MOBILE_IDENTIFIER . '-mst.' . $ext;
        $webpPath      = $path . $resizedSuffix . Config::WEBP_SUFFIX;

        if ($this->configProvider->isFileExist($webpPath)) {
            $source = '<source media="(max-width: 480px)" srcset="' . $this->configProvider->getImageUrl($webpPath)
                . '" type="image/webp"' . $classes . '/>';
        }

        return $source;
    }

    private function getDefaultSource(FileInterface $image, string $path, string $classes, string $query): string
    {
        $ext             = $this->configProvider->getFileExtension($path);
        $resizedSuffix   = '.' . ResponsiveImageService::DESKTOP_IDENTIFIER . '-mst.' . $ext;
        $resizedWebpPath = $path . $resizedSuffix . Config::WEBP_SUFFIX;

        $actualPath = $this->configProvider->isFileExist($resizedWebpPath) ? $resizedWebpPath : $image->getWebpPath();

        return '<source srcset="' . $this->configProvider->getImageUrl($actualPath) . $query
            . '" type="image/webp"' . $classes . '/>';
    }

    private function appendSwatcherFixScript(string $content): string
    {
        $script = '
            <script>
                document.addEventListener("click", function(e) {
                    if (!e.target.classList.contains("swatch-option")) {
                        return;
                    }

                    productElement = e.target.closest(".product-item");

                    if (productElement !== null) {
                        imgSource = productElement.querySelector("source");

                        if (imgSource !== null) {
                            imgSource.srcset = "";
                        }
                    }
                })
            </script>';

        return str_replace('</body>', $script . '</body>', $content);
    }
}
