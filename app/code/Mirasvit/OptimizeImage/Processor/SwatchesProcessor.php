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


namespace Mirasvit\OptimizeImage\Processor;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeImage\Model\ConfigProvider;
use Mirasvit\OptimizeImage\Repository\FileRepository;
use Mirasvit\OptimizeImage\Service\FileListSynchronizationService;

class SwatchesProcessor implements OutputProcessorInterface
{
    private $configProvider;

    private $mediaUrl;

    private $mediaDir;

    private $fileRepository;

    private $syncService;

    public function __construct(
        ConfigProvider $configProvider,
        FileRepository $fileRepository,
        FileListSynchronizationService $syncService,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->fileRepository = $fileRepository;
        $this->syncService    = $syncService;
        $this->mediaUrl       = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $this->mediaDir       = $filesystem->getDirectoryread(DirectoryList::MEDIA);
    }

    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        if (!$this->configProvider->isWebpEnabled()) {
            return $content;
        }

        $swatchOptionsPosition = strpos($content, '"[data-role=swatch-options');

        if ($swatchOptionsPosition !== false) {
            $mainContent = $content;

            $previousScriptPosition = strrpos(substr($content, 0, $swatchOptionsPosition), '<script type="text/x-magento-init">');

            if ($previousScriptPosition !== false) {
                $nextScriptPosition = strpos($content, '</script>', $swatchOptionsPosition);

                if ($nextScriptPosition !== false) {
                    $scriptLength = $nextScriptPosition - $previousScriptPosition + strlen('</script>');
                    $substring    = substr($content, $previousScriptPosition, $scriptLength);

                    $firstBracePosition = strpos($substring, '{');
                    $lastBracePosition  = strrpos($substring, '}');

                    if ($firstBracePosition !== false && $lastBracePosition !== false) {
                        $contentBetweenBraces = substr($substring, $firstBracePosition, $lastBracePosition - $firstBracePosition + 1);

                        $replacement = $this->replaceCallback([$substring, $contentBetweenBraces]);

                        $content = substr_replace($mainContent, $replacement, $previousScriptPosition, $scriptLength);
                    }
                }
            }
        }

        return $content;
    }

    private function replaceCallback(array $match): string
    {
        $config  = SerializeService::decode($match[1]);

        if (!isset($config["[data-role=swatch-options]"])) {
            return $match[0];
        }

        $widgetConfig = $config["[data-role=swatch-options]"];

        $dataKey = array_keys($widgetConfig)[0];

        if (!isset($widgetConfig[$dataKey]['jsonConfig']) || !isset($widgetConfig[$dataKey]['jsonConfig']['images'])) {
            return $match[0];
        }

        foreach ($widgetConfig[$dataKey]['jsonConfig']['images'] as $optionId => $optionData) {
            foreach ($optionData as $idx => $imageConfig) {
                if ($imageConfig["type"] !== 'image' && $imageConfig["type"] !== 'video') {
                    continue;
                }

                $optionData[$idx] = $this->processImageConfig($imageConfig, ['thumb', 'img', 'full']);
            }

            $widgetConfig[$dataKey]['jsonConfig']['images'][$optionId] = $optionData;
        }

        if (isset($widgetConfig[$dataKey]['jsonSwatchConfig'])) {
            foreach ($widgetConfig[$dataKey]['jsonSwatchConfig'] as $option => $optConfig) {
                foreach ($optConfig as $optId => $imageConfig) {
                    if (!isset($imageConfig['type']) || $imageConfig['type'] != 2) {
                        continue;
                    }

                    $optConfig[$optId] = $this->processImageConfig($imageConfig, ['value', 'thumb']);
                }

                $widgetConfig[$dataKey]['jsonSwatchConfig'][$option] = $optConfig;
            }
        }

        $config["[data-role=swatch-options]"] = $widgetConfig;

        $serializedKey = str_replace('/', '\/', $dataKey);

        $script = SerializeService::encode($config);
        $script = str_replace($serializedKey, $dataKey, $script);

        return '<script type="text/x-magento-init">' . $script . '</script>';
    }

    private function processImageConfig(array $imageConfig, array $allowedKeys): array
    {
        foreach ($imageConfig as $key => $value) {
            if (!in_array($key, $allowedKeys) || strpos($value, '.webp') !== false) {
                continue;
            }

            preg_match('/\?.*/is', $value, $query);

            $query = count($query) ? $query[0] : '';
            $value = str_replace($query, '', $value);

            $absolutePath = $this->configProvider->retrieveImageAbsPath($value);

            if (!$absolutePath) {
                continue;
            }

            $image = $this->syncService->ensureFile($absolutePath);

            if ($image && $image->getWebpPath()) {
                $webpPath = $this->configProvider->getAbsolutePath($image->getWebpPath());

                if (!file_exists($webpPath)) {
                    $image->setWebpPath(null);
                    $this->fileRepository->save($image);

                    continue;
                }

                $webpUrl = $this->configProvider->getImageUrl($image->getWebpPath());

                $imageConfig[$key] = $webpUrl . $query;
            }
        }

        return $imageConfig;
    }
}
