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

namespace Mirasvit\OptimizeImage\Console\Command;

use Mirasvit\OptimizeImage\Model\ConfigProvider;
use Mirasvit\OptimizeImage\Repository\FileRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends Command
{
    private $configProvider;

    private $fileRepository;

    public function __construct(
        ConfigProvider $configProvider,
        FileRepository $fileRepository
    ) {
        $this->configProvider = $configProvider;
        $this->fileRepository = $fileRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mirasvit:optimize-image:reset')
            ->setDescription('Remove compressed and webp images');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start restoring images');

        foreach ($this->fileRepository->getCollection() as $file) {
            $absOptimizedPath = $file->getOptimizedPath()
                ? $this->configProvider->getAbsolutePath($file->getOptimizedPath())
                : null;

            if ($absOptimizedPath && file_exists($absOptimizedPath)) {
                unlink($absOptimizedPath);
            }

            $absWebpPath = $file->getWebpPath()
                ? $this->configProvider->getAbsolutePath($file->getWebpPath())
                : null;

            if ($absWebpPath && file_exists($absWebpPath)) {
                unlink($absWebpPath);
            }

            $origAbsPath = $this->configProvider->getAbsolutePath($file->getRelativePath());

            if (file_exists($origAbsPath)) {
                $file->setCompression(100)
                    ->setOriginalSize(filesize($origAbsPath))
                    ->setOptimizedPath(null)
                    ->setWebpPath(null)
                    ->setActualSize(null)
                    ->setProcessedAt(null);

                $this->fileRepository->save($file);
            } else {
                $this->fileRepository->delete($file);
            }

        }

        $output->writeln("Done!");

        return 0;
    }
}
