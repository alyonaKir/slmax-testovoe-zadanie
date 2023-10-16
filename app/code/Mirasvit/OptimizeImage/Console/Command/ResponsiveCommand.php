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
use Mirasvit\OptimizeImage\Service\ResponsiveImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResponsiveCommand extends Command
{
    private $configProvider;

    private $responsiveImageService;

    public function __construct(
        ConfigProvider $configProvider,
        ResponsiveImageService $responsiveImageService
    ) {
        $this->configProvider         = $configProvider;
        $this->responsiveImageService = $responsiveImageService;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mirasvit:optimize-image:responsive')
            ->setDescription('Various commands');

        $this->addOption('generate', null, null, 'Generate resized image files');
        $this->addOption('cleanup', null, null, 'Remove generated resized image files');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('generate')) {
            $output->writeln('Generating resized images');
            try {
                if ($this->responsiveImageService->generate()) {
                    $output->writeln('Done!');
                } else {
                    $output->writeln('No config provided for resizing images. Please add corresponded settings to the configurations of the extension');
                }
            } catch (\Exception $e) {
                $output->writeln('Resized image files generation failed. Reason: ' . $e->getMessage());
            }
        } elseif ($input->getOption('cleanup')) {
            $output->writeln('Deleting resized images');
            try {
                $this->responsiveImageService->cleanup();
                $output->writeln('Done!');
            } catch (\Exception $e) {
                $output->writeln('Resized image files deletion failed. Reason: ' . $e->getMessage());
            }
        } else {
            $output->writeln('Please use one of the options depends on the task:');
            $output->writeln('--generate - Generate resized image files');
            $output->writeln('--cleanup - Remove generated resized image files');
        }

        return 0;
    }
}
