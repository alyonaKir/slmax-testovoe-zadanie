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
use Mirasvit\OptimizeImage\Service\FileListBatchService;
use Mirasvit\OptimizeImage\Service\FileListSynchronizationService;
use Mirasvit\OptimizeImage\Service\FormatService;
use Mirasvit\OptimizeImage\Service\OptimizeService;
use Mirasvit\OptimizeImage\Service\WebpService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeCommand extends Command
{
    private $fileListSynchronizationService;

    private $fileRepository;

    private $fileListBatchService;

    private $optimizeService;

    private $webpService;

    private $formatService;

    private $configProvider;

    public function __construct(
        FileListSynchronizationService $fileListSynchronizationService,
        FileRepository $fileRepository,
        FileListBatchService $fileListBatchService,
        OptimizeService $optimizeService,
        WebpService $webpService,
        FormatService $formatService,
        ConfigProvider $configProvider
    ) {
        $this->fileListSynchronizationService = $fileListSynchronizationService;
        $this->fileRepository                 = $fileRepository;
        $this->fileListBatchService           = $fileListBatchService;
        $this->optimizeService                = $optimizeService;
        $this->webpService                    = $webpService;
        $this->formatService                  = $formatService;
        $this->configProvider                 = $configProvider;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:optimize-image:optimize')
            ->setDescription('Run images optimization process');

        $this->addOption('image', null, null, 'Optimize original images');
        $this->addOption('webp', null, null, 'Generate webp images');

        parent::configure();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->configProvider->isFilesystemStrategy()) {
            $this->fileListSynchronizationService->synchronize(1000);
        }

        if (!$input->getOption('image') && !$input->getOption('webp')) {
            $input->setOption('image', true);
            $input->setOption('webp', true);
        }

        if ($input->getOption('webp') && !$this->configProvider->isWebpEnabled()) {
            $output->writeln('<comment>WEBP images disabled in the admin panel and will not be generated</comment>');
        }

        $size = $this->fileListBatchService->getSize();

        $bar = new ProgressBar($output, $size);
        $bar->setFormat('%current%(%updated%)/%max% [%bar%] %percent%% <info>%message%</info> %etc%');

        $bar->setMessage('', 'message');
        $bar->setMessage('0', 'updated');
        $bar->setMessage('', 'etc');

        $originalSize = 0;
        $actualSize   = 0;
        $updatedFiles = 0;

        while ($batch = $this->fileListBatchService->getBatch()) {
            foreach ($batch as $file) {
                try {
                    $path = $file->getRelativePath();

                    if ($input->getOption('image')) {
                        $this->optimizeService->optimize($file);

                        if ($optPath = $file->getOptimizedPath()) {
                            if (!file_exists($this->configProvider->getAbsolutePath($optPath))) {
                                usleep(100);
                            }

                            $path = $optPath;
                        }
                    }

                    if ($input->getOption('webp') && $this->configProvider->isWebpEnabled()) {
                        $this->webpService->process($file);

                        if ($webpPath = $file->getWebpPath()) {
                            if (!file_exists($this->configProvider->getAbsolutePath($webpPath))) {
                                usleep(100);
                            }

                            $path = $webpPath;
                        }
                    }

                    if ($file->getRelativePath() !== $path) {
                        $file->setActualSize(filesize($this->configProvider->getAbsolutePath($path)))
                            ->setCompression($this->configProvider->getCompressionLevel());
                    }

                    $originalSize += $file->getOriginalSize();
                    $actualSize   += $file->getActualSize();

                    if ($file->getOriginalSize() != $file->getActualSize()) {
                        $updatedFiles++;
                    }

                    $this->fileRepository->save($file);
                } catch (\Exception $e) {
                    $this->fileRepository->delete($file);

                    $output->writeln('');
                    $output->writeln($e->getMessage());
                }

                $bar->advance();

                $message = [
                    sprintf('Saved: %s', $this->formatService->formatBytes($originalSize - $actualSize)),
                    sprintf('Processed: %s', $this->formatService->formatBytes($originalSize)),
                ];
                $bar->setMessage(implode(' ', $message), 'message');
                $bar->setMessage((string)$updatedFiles, 'updated');

                $savedSize = $file->getOriginalSize() - $file->getActualSize();
                $bar->setMessage($file->getBasename() . ' ' . $this->formatService->formatBytes($savedSize), 'etc');
            }
        }

        $bar->clear();

        $output->writeln(sprintf("<info>Processed files:</info> %s", $updatedFiles));
        $output->writeln(sprintf("<info>Original size:</info>   %s", $this->formatService->formatBytes($originalSize)));
        $output->writeln(sprintf("<info>Actual size:</info>     %s", $this->formatService->formatBytes($actualSize)));
        $output->writeln(sprintf("<info>Saved size:</info>      %s", $this->formatService->formatBytes($originalSize - $actualSize)));

        return 0;
    }
}
