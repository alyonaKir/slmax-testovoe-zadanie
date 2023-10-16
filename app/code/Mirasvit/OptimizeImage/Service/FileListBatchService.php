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
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Model\ConfigProvider;
use Mirasvit\OptimizeImage\Model\ResourceModel\File\Collection;
use Mirasvit\OptimizeImage\Repository\FileRepository;

class FileListBatchService
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

    public function getSize(): int
    {
        return (int)$this->getUnprocessedCollection()->getSize();
    }

    public function getBatch(int $batchSize = 100): ?Collection
    {
        $collection = $this->getUnprocessedCollection();
        $collection->setPageSize($batchSize);

        if ($collection->count() === 0) {
            return null;
        }

        return $collection;
    }

    public function getUnprocessedCollection(): Collection
    {
        $collection = $this->fileRepository->getCollection();

        $collection->addFieldToFilter([
            FileInterface::ACTUAL_SIZE, FileInterface::PROCESSED_AT, FileInterface::COMPRESSION,
        ], [
            ['null' => true],
            ['lteq' => date('Y-m-d H:i:s', time() - 365 * 24 * 60 * 60),],
            ['neq' => $this->configProvider->getCompressionLevel()],
        ])->setOrder(FileInterface::ID);

        return $collection;
    }
}
