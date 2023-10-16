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

class WebpService
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

    public function process(FileInterface $file): FileInterface
    {
        if (strpos($file->getRelativePath(), '"') !== false) {
            return $file;
        }

        if (!$this->validationService->canConvertWebp()) {
            return $file;
        }

        switch ($file->getFileExtension()) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                $path = $this->generateWebp($file);
                $file->setWebpPath($path);

                break;
        }

        return $file;
    }

    private function generateWebp(FileInterface $file): ?string
    {
        $path = $this->configProvider->getAbsolutePath($file->getRelativePath());

        if (!file_exists($path)) {
            throw new NotFoundException(__('The file was removed: %1', $path));
        }

        $fileExtension = $this->configProvider->getFileExtension($path);

        $command = $fileExtension == 'gif'
            ? ConfigProvider::CMD_PROCESS_GIF2WEBP
            : ConfigProvider::CMD_PROCESS_WEBP;

        $optPath         = $this->configProvider->getOptPathFromRelativePath($file->getRelativePath());
        $optAbsolutePath = $this->configProvider->getAbsolutePath($optPath);
        
        // file extension can be lowercase and uppercase (jpg, JPG)
        if (!strrpos($optPath, '.' . $fileExtension)) {
            $fileExtension = strtoupper($fileExtension);
            
            if (!strrpos($optPath, '.' . $fileExtension)) {
                return null;
            }
        }

        $this->fileService->ensureDir($optAbsolutePath);

        $configCompression = $this->configProvider->getCompressionLevel();
        $webpRelativePath  = substr($optPath, 0, strrpos($optPath, '.' . $fileExtension)) . ConfigProvider::WEBP_SUFFIX;
        $newPath           = $this->configProvider->getAbsolutePath($webpRelativePath);

        if (file_exists($newPath) && $file->getCompression() == $configCompression) {
            return $webpRelativePath;
        }

        try {
            $this->shell->execute(sprintf($command, $configCompression, $path, $newPath));
        } catch (\Exception $e) {
            if ($convertedPath = $this->normalize($path)) {
                $this->shell->execute(sprintf($command, $configCompression, $convertedPath, $newPath));
                unlink($convertedPath);
            }
        }

        return $webpRelativePath;
    }

    private function normalize(string $path): ?string
    {
        $convertedPath = $path . ConfigProvider::CONVERT_SUFFIX;

        try {
            $this->shell->execute(sprintf(ConfigProvider::CMD_CONVERT_RGB, $path, $convertedPath));

            return $convertedPath;
        } catch (\Exception $e) {
            return null;
        }
    }
}
