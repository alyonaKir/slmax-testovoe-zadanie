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

namespace Mirasvit\OptimizeImage\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;

class File extends AbstractModel implements FileInterface
{
    public function getBasename(): string
    {
        return $this->getData(self::BASENAME);
    }

    public function setBasename(string $value): FileInterface
    {
        return $this->setData(self::BASENAME, $value);
    }

    public function getRelativePath(): string
    {
        return $this->getData(self::RELATIVE_PATH);
    }

    public function setRelativePath(string $value): FileInterface
    {
        return $this->setData(self::RELATIVE_PATH, $value);
    }

    public function getFileExtension(): string
    {
        return $this->getData(self::FILE_EXTENSION);
    }

    public function setFileExtension(string $value): FileInterface
    {
        return $this->setData(self::FILE_EXTENSION, $value);
    }

    public function getWebpPath(): ?string
    {
        return $this->getData(self::WEBP_PATH) ? : null;
    }

    public function setWebpPath(?string $value): FileInterface
    {
        return $this->setData(self::WEBP_PATH, $value);
    }

    public function getOriginalSize(): int
    {
        return (int)$this->getData(self::ORIGINAL_SIZE);
    }

    public function setOriginalSize(int $value): FileInterface
    {
        return $this->setData(self::ORIGINAL_SIZE, $value);
    }

    public function getActualSize(): ?int
    {
        $size = $this->getData(self::ACTUAL_SIZE);

        return $size ? (int)$size : null;
    }

    public function setActualSize(?int $value): FileInterface
    {
        return $this->setData(self::ACTUAL_SIZE, $value);
    }

    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getProcessedAt(): ?string
    {
        return $this->getData(self::PROCESSED_AT) ? : null;
    }

    public function setProcessedAt(?string $value): FileInterface
    {
        return $this->setData(self::PROCESSED_AT, $value);
    }

    public function getCompression(): int
    {
        return (int)$this->getData(self::COMPRESSION);
    }

    public function setCompression(int $value): FileInterface
    {
        return $this->setData(self::COMPRESSION, $value);
    }

    public function getOptimizedPath(): ?string
    {
        $path = $this->getData(self::OPTIMIZED_PATH);

        return $path ? : null;
    }

    public function setOptimizedPath(?string $value): FileInterface
    {
        return $this->setData(self::OPTIMIZED_PATH, $value);
    }

    public function getRelativePathToUse(): string
    {
        return $this->getOptimizedPath() ? : $this->getRelativePath();
    }

    public function getWebRelativePath(): string
    {
        return str_replace('pub/media/', '', $this->getRelativePathToUse());
    }

    public function getWebWebpPath(): ?string
    {
        return $this->getWebpPath() ? str_replace('pub/media/', '', $this->getWebpPath()) : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\File::class);
    }
}
