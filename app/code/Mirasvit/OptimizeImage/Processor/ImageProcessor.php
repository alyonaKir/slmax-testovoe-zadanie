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


use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Model\ConfigProvider;
use Mirasvit\OptimizeImage\Repository\FileRepository;
use Mirasvit\OptimizeImage\Service\FileListSynchronizationService;
use Mirasvit\OptimizeImage\Service\FormatService;
use Mirasvit\OptimizeImage\Service\ResponsiveImageService;

class ImageProcessor implements OutputProcessorInterface
{
    private $configProvider;

    private $fileRepository;

    private $formatService;

    private $syncService;

    public function __construct(
        ConfigProvider $configProvider,
        FileRepository $fileRepository,
        FormatService $formatService,
        FileListSynchronizationService $syncService
    ) {
        $this->configProvider = $configProvider;
        $this->fileRepository = $fileRepository;
        $this->formatService  = $formatService;
        $this->syncService    = $syncService;
    }

    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        // Case 1: images with js code after src
        $content = preg_replace_callback(
            '/(<\s*img[^>]+)src\s*=\s*["\']([^"\'\?]+)(\?[^"\']*)?[\'"]([^>]{0,}(?:=>)+[^>]{0,}>)/is',
            [$this, 'replaceCallback'],
            $content
        );

        // Case 2: images with possible js code before src
        $content = preg_replace_callback(
            '/(<\s*img[^>]+(?:=>[^>]+)*)src\s*=\s*["\']([^"\'\?]+)(\?[^"\']*)?[\'"]([^>]{0,}>)/is',
            [$this, 'replaceCallback'],
            $content
        );

        return $content;
    }

    private function replaceCallback(array $match): string
    {
        $absolutePath = $this->configProvider->retrieveImageAbsPath($match[2]);

        if (!$absolutePath) {
            return $match[0];
        }

        if (!$this->configProvider->isFilesystemStrategy()) {
            $this->syncService->ensureFile($absolutePath);
        }

        $relativePath = $this->configProvider->getRelativePath($absolutePath);
        $replacement  = $match[0];

        $file = $this->fileRepository->getByRelativePath($relativePath);
        $ext  = $this->configProvider->getFileExtension($absolutePath);

        if (!$file) {
            return $replacement;
        }

        if ($this->configProvider->isWebpEnabled()) {
            $replacement = $this->replaceWebp($file, $replacement, $match[2]);
        }
        $replacement = $this->replaceOptimized($file, $replacement, $match[2]);
        $replacement = $this->replaceResponsive($file, $replacement, $match[2]);

        if (!$this->configProvider->isDebug()) {
            return $replacement;
        }

        if ($file && $file->getProcessedAt()) {
            $saved   = $file->getOriginalSize() - $file->getActualSize();
            $saved   = $this->formatService->formatBytes($saved);
            $hasWebp = $file->getWebpPath() ? "Yes" : "No";

            $info = "<span>Optimized. Saved $saved</span>
                     <span>Webp generated - $hasWebp</span>";
        } elseif (!$this->configProvider->isAllowedFileExtension($ext)) {
            $info = "<span>Not allowed file extension $ext</span>";
        } else {
            $info = "<span>Not processed yet</span>";
        }

        return $replacement .= "<span class='mst-optwebp-debug'>$info</span>";
    }

    private function replaceWebp(FileInterface $file, string $image, string $originalUrl): string
    {
        if (!$file->getWebpPath() || $this->configProvider->isWebpException($image)) {
            return $image;
        }

        if (!file_exists($this->configProvider->getAbsolutePath($file->getWebpPath()))) {
            return $image;
        }

        return str_replace($originalUrl, $this->configProvider->getImageUrl($file->getWebWebpPath()), $image);
    }

    private function replaceOptimized(FileInterface $file, string $image, string $originalUrl): string
    {
        if (!$file->getOptimizedPath() || !file_exists($this->configProvider->getAbsolutePath($file->getOptimizedPath()))) {
            return $image; // not optimized yet
        }

        if ($file->getWebpPath() && strpos($image, $file->getWebpPath()) !== false) {
            return $image; // already replaced with webp
        }

        return str_replace($originalUrl, $this->configProvider->getImageUrl($file->getWebRelativePath()), $image);
    }

    private function replaceResponsive(FileInterface $file, string $image, string $originalUrl): string
    {
        $responsiveImageConfig = $this->configProvider->getResponsiveImageConfigByFileName($originalUrl);

        if (!$responsiveImageConfig) {
            return $image;
        }

        $pathToUse = $this->configProvider->getOptPathFromRelativePath($file->getRelativePath());

        // mobile resized image
        $mobileFile = $this->fileRepository->getByRelativePath(
            $this->configProvider->getResizedPath($file, $pathToUse, ResponsiveImageService::MOBILE_IDENTIFIER)
        );

        if (!$mobileFile) {
            return $image; // responsive mobile image not generated
        }

        $mobileImageRelativePath = $this->shouldReplaceWithWebp($mobileFile)
            ? $mobileFile->getWebpPath()
            : $mobileFile->getRelativePath();

        if (!file_exists($this->configProvider->getAbsolutePath($mobileImageRelativePath))) {
            return $image; // resized image removed manually
        }

        $mobileUrl = $this->configProvider->getImageUrl($mobileImageRelativePath);

        // desktop resized image
        $desktopFile = $this->fileRepository->getByRelativePath(
            $this->configProvider->getResizedPath($file, $pathToUse, ResponsiveImageService::DESKTOP_IDENTIFIER)
        );

        $desktopUrl = $this->configProvider->getImageUrl($file->getRelativePathToUse());

        if ($desktopFile) {
            $desktopImageRelativePath = $this->shouldReplaceWithWebp($desktopFile)
                ? $desktopFile->getWebpPath()
                : $desktopFile->getRelativePath();

            if (file_exists($this->configProvider->getAbsolutePath($desktopImageRelativePath))) {
                $desktopUrl = $this->configProvider->getImageUrl($desktopImageRelativePath);
            }
        }

        $srcset     = $mobileUrl . ' 480w, ' . $desktopUrl . ' 800w';
        $sizes      = "(max-width: 480px) 480px, 800px";
        $additional = 'srcset="' . $srcset . '" sizes="' . $sizes . '"';

        return str_replace('<img', '<img ' . $additional, $image);
    }

    private function shouldReplaceWithWebp(FileInterface $file)
    {
        return $this->configProvider->isWebpEnabled()
            && $file->getWebpPath()
            && file_exists($this->configProvider->getAbsolutePath($file->getWebpPath()));
    }
}
