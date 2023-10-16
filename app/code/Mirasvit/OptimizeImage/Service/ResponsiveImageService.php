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

namespace Mirasvit\OptimizeImage\Service;


use Magento\Framework\Filesystem;
use Magento\Framework\Shell;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Model\ConfigProvider;
use Mirasvit\OptimizeImage\Repository\FileRepository;

class ResponsiveImageService
{
    const RESIZE_COMMAND     = 'convert "%s" -resize %sx "%s"';
    const MOBILE_IDENTIFIER  = '480';
    const DESKTOP_IDENTIFIER = '800';

    private $fileSyncService;

    private $fileRepository;

    private $shell;

    private $configProvider;

    public function __construct(
        FileListSynchronizationService $fileSyncService,
        FileRepository $fileRepository,
        Shell $shell,
        ConfigProvider $configProvider
    ) {
        $this->fileSyncService = $fileSyncService;
        $this->fileRepository  = $fileRepository;
        $this->shell           = $shell;
        $this->configProvider  = $configProvider;
    }

    public function getResizeDirName(string $identifier): string
    {
        return 'resize-' . $identifier . '/';
    }

    public function generate(): bool
    {
        $responsiveImages = $this->configProvider->getResponsiveImages();

        if (!$responsiveImages) {
            return false;
        }

        foreach ($responsiveImages as $imageConfig) {

            $fileCollection = $this->fileRepository
                ->getCollection()
                ->addFieldToFilter(FileInterface::RELATIVE_PATH, ['like' => '%' . $imageConfig['file'] . '%']);

            foreach ($fileCollection as $file) {
                $absPath = $this->configProvider->getAbsolutePath($file->getRelativePath());

                if (!file_exists($absPath)) {
                    continue;
                }

                if (
                    strpos($absPath, $this->configProvider->getResizeDirName(self::MOBILE_IDENTIFIER)) !== false
                    || strpos($absPath, $this->configProvider->getResizeDirName(self::DESKTOP_IDENTIFIER)) !== false
                ) {
                    continue;
                }

                $this->resize($file, $imageConfig, self::MOBILE_IDENTIFIER);
                $this->resize($file, $imageConfig, self::DESKTOP_IDENTIFIER);
            }

        }

        return true;
    }

    public function cleanup(): bool
    {
        $responsiveImages = $this->configProvider->getResponsiveImages();

        if (!$responsiveImages) {
            return true;
        }

        foreach ($responsiveImages as $imageConfig) {
            $fileCollection = $this->fileRepository
                ->getCollection()
                ->addFieldToFilter(
                    [FileInterface::RELATIVE_PATH, FileInterface::RELATIVE_PATH],
                    [
                        ['like' => '%/' . ConfigProvider::RESIZED_DIR_PREFIX . '%/%' . $imageConfig['file'] . '%'],
                        ['like' => '%' . $imageConfig['file'] . '%/' . ConfigProvider::RESIZED_DIR_PREFIX . '%/%'],
                    ]
                );

            foreach ($fileCollection as $file) {
                $imageAbsPath = $this->configProvider->getAbsolutePath($file->getRelativePath());
                $webpAbsPath  = $file->getWebpPath() ? $this->configProvider->getAbsolutePath($file->getWebpPath()) : null;

                if (file_exists($imageAbsPath)) {
                    unlink($imageAbsPath);
                }

                if ($webpAbsPath && file_exists($webpAbsPath)) {
                    unlink($webpAbsPath);
                }

                $this->fileRepository->delete($file);
            }
        }

        return true;
    }

    private function resize(FileInterface $file, array $imageConfig, string $identifier): void
    {
        $path    = $this->configProvider->getAbsolutePath($file->getRelativePath());
        $optPath = $this->configProvider->getAbsolutePath(
            $this->configProvider->getOptPathFromRelativePath($file->getRelativePath())
        );

        $resizedPath = $this->configProvider->getResizedPath($file, $optPath, $identifier);

        $this->fileSyncService->ensureDir($resizedPath);

        if (file_exists($resizedPath) || !(int)$imageConfig[$identifier]) {
            return;
        }

        $this->shell->execute(sprintf(
            self::RESIZE_COMMAND,
            $path,
            $imageConfig[$identifier],
            $resizedPath
        ));

        $file = $this->fileSyncService->ensureFile($resizedPath);
    }
}
