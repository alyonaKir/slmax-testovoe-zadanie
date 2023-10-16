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

namespace Mirasvit\OptimizeImage\Api\Data;

interface FileInterface
{
    const TABLE_NAME = 'mst_optimize_image_file';

    const ID             = 'file_id';
    const BASENAME       = 'basename';
    const RELATIVE_PATH  = 'relative_path';
    const OPTIMIZED_PATH = 'optimized_path';
    const FILE_EXTENSION = 'file_extension';
    const WEBP_PATH      = 'webp_path';
    const ORIGINAL_SIZE  = 'original_size';
    const ACTUAL_SIZE    = 'actual_size';
    const CREATED_AT     = 'created_at';
    const PROCESSED_AT   = 'processed_at';
    const COMPRESSION    = 'compression';

    /**
     * @return int
     */
    public function getId();

    public function getBasename(): string;

    public function setBasename(string $value): self;

    public function getRelativePath(): string;

    public function setRelativePath(string $value): self;

    public function getOptimizedPath(): ?string;

    public function setOptimizedPath(?string $value): self;

    public function getFileExtension(): string;

    public function setFileExtension(string $value): self;

    public function getWebpPath(): ?string;

    public function setWebpPath(?string $value): self;

    public function getOriginalSize(): int;

    public function setOriginalSize(int $value): self;

    public function getActualSize(): ?int;

    public function setActualSize(?int $value): self;

    public function getCreatedAt(): string;

    public function getProcessedAt(): ?string;

    public function setProcessedAt(?string $value): self;

    public function getCompression(): int;

    public function setCompression(int $value): self;
}
