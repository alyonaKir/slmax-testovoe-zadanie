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

namespace Mirasvit\OptimizeImage\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Api\Data\FileInterfaceFactory;
use Mirasvit\OptimizeImage\Model\ResourceModel\File\Collection;
use Mirasvit\OptimizeImage\Model\ResourceModel\File\CollectionFactory;

class FileRepository
{
    private $entityManager;

    private $collectionFactory;

    private $factory;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        FileInterfaceFactory $factory
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $factory;
    }

    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function create(): FileInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?FileInterface
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        if (!$model->getId()) {
            return null;
        }

        return $model;
    }

    public function getByRelativePath(string $relativePath): ?FileInterface
    {
        /** @var \Mirasvit\OptimizeImage\Model\File $model */
        $model = $this->create();
        $model->load($relativePath, FileInterface::RELATIVE_PATH);

        if (!$model->getId()) {
            return null;
        }

        return $model;
    }

    public function getByOptimizedPath(string $relativePath): ?FileInterface
    {
        /** @var \Mirasvit\OptimizeImage\Model\File $model */
        $model = $this->create();
        $model->load($relativePath, FileInterface::OPTIMIZED_PATH);

        if (!$model->getId()) {
            return null;
        }

        return $model;
    }

    public function save(FileInterface $model): FileInterface
    {
        return $this->entityManager->save($model);
    }

    public function delete(FileInterface $model): void
    {
        $this->entityManager->delete($model);
    }
}
