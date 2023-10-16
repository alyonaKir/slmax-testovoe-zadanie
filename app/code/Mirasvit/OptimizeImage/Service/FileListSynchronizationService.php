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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Model\ConfigProvider;
use Mirasvit\OptimizeImage\Repository\FileRepository;

class FileListSynchronizationService
{
    private $configProvider;

    private $fileRepository;

    private $fs;

    public function __construct(
        ConfigProvider $configProvider,
        FileRepository $fileRepository,
        Filesystem $fs
    ) {
        $this->configProvider = $configProvider;
        $this->fileRepository = $fileRepository;
        $this->fs             = $fs;
    }

    public function synchronize(int $limit): void
    {
        foreach ($this->getSyncPaths() as $syncPath) {
            $files = $this->scanDir($syncPath, $limit);
            shuffle($files);

            foreach ($files as $file) {
                $this->ensureFile($file);
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function ensureFile(string $file): ?FileInterface
    {
        $extension = $this->configProvider->getFileExtension($file);

        if (!$this->configProvider->isAllowedFileExtension($extension)) {
            return null;
        }

        $pathInfo     = pathinfo($file);
        $relativePath = $this->configProvider->getRelativePath($file);
        $size         = filesize($file);

        $model = $this->fileRepository->getByRelativePath($relativePath);

        if (!$model) {
            $model = $this->fileRepository->create();
            $model->setBasename($pathInfo['basename'])
                ->setRelativePath($relativePath)
                ->setFileExtension($extension)
                ->setOriginalSize($size);

            if (strpos($relativePath, 'pub/media') === 0) {
                $this->fileRepository->save($model);
            } elseif ($optimizedPath = $this->copyFileToOptDir($relativePath)) {
                $model->setOptimizedPath($optimizedPath);

                $this->fileRepository->save($model);
            }
        } else {
            $optimizedPath = $model->getOptimizedPath();

            $optSize = $optimizedPath && file_exists($this->configProvider->getAbsolutePath($optimizedPath))
                ? filesize($this->configProvider->getAbsolutePath($optimizedPath))
                : null;

            $webpPath = $model->getWebpPath();

            if ($this->configProvider->isWebpEnabled() && $webpPath && file_exists($this->configProvider->getAbsolutePath($webpPath))) {
                $optSize = filesize($this->configProvider->getAbsolutePath($webpPath));
            }

            if (!$optSize || $model->getActualSize() && $model->getActualSize() != $optSize) {
                $model->setActualSize(null)
                    ->setOptimizedPath(null)
                    ->setOriginalSize($size);

                $this->fileRepository->save($model);
            }
        }

        return $model;
    }

    public function copyFileToOptDir(string $relativePath): ?string
    {
        $rootPath           = $this->fs->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        $relativeOptDirPath = 'pub/media/' . ConfigProvider::OPTIMIZE_DIR_NAME;

        if (strpos($relativePath, $relativeOptDirPath) !== false) { // for responsive images
            return $relativePath;
        }

        $pathStart       = strpos($relativePath, 'pub/media/') === 0 ? 'pub/media' : 'pub';
        $optRelativePath = $relativeOptDirPath . substr($relativePath, strlen($pathStart));

        $res = $this->fs->getDirectoryWrite(DirectoryList::ROOT)
            ->copyFile($rootPath . $relativePath, $rootPath . $optRelativePath);

        return $res ? $optRelativePath : null;
    }

    public function ensureDir(string $absolutePath): void
    {
        $dirPath = preg_replace('/\/[^\/]*$/', '', $absolutePath);

        if (!$this->fs->getDirectoryRead(DirectoryList::ROOT)->isExist($dirPath)) {
            $this->fs->getDirectoryWrite(DirectoryList::ROOT)->create($dirPath);
        }
    }

    public function getSyncPaths(): array
    {
        $mediaDirectory = $this->fs->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $catalogDirectory = $mediaDirectory . 'catalog/';

        return [
            $catalogDirectory,
        ];
    }

    public function scanDir(string $target, int $limit): array
    {
        $files = [];
        if (is_dir($target) && strpos($target, '/pub/media/' . ConfigProvider::OPTIMIZE_DIR_NAME) === false) {
            $items = glob($target . '*', GLOB_MARK);
            shuffle($items);

            foreach ($items as $item) {
                if (is_file($item)) {
                    $files[] = $item;
                } else {
                    $files = array_merge($files, $this->scanDir($item, $limit - count($files)));
                }

                if (count($files) >= $limit) {
                    break;
                }
            }
        }

        return $files;
    }
}
