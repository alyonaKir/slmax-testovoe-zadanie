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

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Shell;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Model\ConfigProvider;

class OptimizeService
{
    private $shell;

    private $configProvider;

    private $validationService;

    private $fileService;

    public function __construct(
        Shell $shell,
        ConfigProvider $configProvider,
        ValidationService $validationService,
        FileListSynchronizationService $fileService
    ) {
        $this->shell             = $shell;
        $this->configProvider    = $configProvider;
        $this->validationService = $validationService;
        $this->fileService       = $fileService;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function optimize(FileInterface $file): FileInterface
    {
        if (strpos($file->getRelativePath(), '"') !== false) {
            return $file;
        }

        $relativePath = $file->getRelativePath();
        $absPath      = $this->configProvider->getAbsolutePath($relativePath);
        $optPath      = $this->configProvider->getAbsolutePath($this->configProvider->getOptPathFromRelativePath($relativePath));

        if (!file_exists($absPath)) {
            throw new NotFoundException(__('The file was removed: %1', $absPath));
        }

        switch ($file->getFileExtension()) {
            case 'jpg':
            case 'jpeg':
                $this->processJpg($file);
                break;
            case 'png':
                $this->processPng($file);
                break;
            case 'gif':
                $this->processGif($file);
                break;
        }

        return $file;
    }

    private function processJpg(FileInterface $file): void
    {
        if (!$this->validationService->canRunOptimizationFor('jpg')) {
            return;
        }

        $optPath = $this->fileService->copyFileToOptDir($file->getRelativePath());

        if (!$optPath) {
            return; //something went wrong while copying file
        }

        $command = ConfigProvider::CMD_PROCESS_JPG;

        if ($this->configProvider->getCompressionLevel() !== 100) {
            $command .= ' --max=' . $this->configProvider->getCompressionLevel();
        }

        $this->shell->execute(sprintf($command, $this->configProvider->getAbsolutePath($optPath)));
        $file->setOptimizedPath($optPath);
    }

    private function processPng(FileInterface $file): void
    {
        if (!$this->validationService->canRunOptimizationFor('png')) {
            return;
        }

        $optPath = $this->fileService->copyFileToOptDir($file->getRelativePath());

        if ($optPath) {
            $command = ConfigProvider::CMD_PROCESS_PNG;
            $this->shell->execute(sprintf($command, $this->configProvider->getAbsolutePath($optPath)));
            $file->setOptimizedPath($optPath);
        }
    }

    private function processGif(FileInterface $file): void
    {
        if (!$this->validationService->canRunOptimizationFor('gif')) {
            return;
        }

        $optPath = $this->fileService->copyFileToOptDir($file->getRelativePath());

        if (!$optPath) {
            return; //something went wrong while copying file
        }

        $command = ConfigProvider::CMD_PROCESS_GIF;

        if ($this->configProvider->getCompressionLevel() !== 100) {
            $command .= ' --lossy=' . $this->getGifCompressionLevel();
        }

        $this->shell->execute(sprintf($command, $optPath, $optPath));
        $file->setOptimizedPath($optPath);
    }

    /**
     * Convert image quality level to gif compression level
     */
    private function getGifCompressionLevel(): int
    {
        // default gif compression is 20
        // higher value gives higher compression

        return 120 - $this->configProvider->getCompressionLevel();
    }
}
