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

class FotoramaProcessor implements OutputProcessorInterface
{
    private $configProvider;

    private $fileRepository;

    private $syncService;

    private $mediaUrl;

    private $mediaDir;

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

        $content = preg_replace_callback(
            '/<script type="text\/x-magento-init">([^<]*?magnifier\\\\?\/magnify.*?)<\/script>/ims',
            [$this, 'replaceCallback'],
            $content
        );

        $content = preg_replace_callback(
            '/<div[^>]*class=["\']notorama[^"\']*["\'][^>]*data-mage-init=[\']([^\']*)[\'][^>]*>/ims',
            [$this, 'replaceNotoramaCallback'],
            $content
        );

        $content = preg_replace_callback(
            '/<div[^>]*data-mage-init=[\']([^\']*)[\'][^>]*>/ims',
            [$this, 'replaceBreezeMageInitCallback'],
            $content
        );

        $content = preg_replace_callback(
            '/<script type="text\/x-magento-init">([^<]*?data-gallery-id.*?)<\/script>/ims',
            [$this, 'replaceBreezeScriptCallback'],
            $content
        );

        return $content;
    }

    private function replaceCallback(array $match): string
    {
        $config       = SerializeService::decode($match[1]);
        $widgetConfig = $config["[data-gallery-role=gallery-placeholder]"];
        $dataKey      = array_keys($widgetConfig)[0];

        if (!isset($widgetConfig[$dataKey]["data"])) {
            return $match[0];
        }

        $imagesConfig = $this->prepareImagesConfig($widgetConfig[$dataKey]["data"]);

        $config["[data-gallery-role=gallery-placeholder]"][$dataKey]["data"] = $imagesConfig;

        $serializedKey = str_replace('/', '\/', $dataKey);

        $script = SerializeService::encode($config);
        $script = str_replace($serializedKey, $dataKey, $script);
        $script = str_replace('magnifier\/magnify', 'magnifier/magnify', $script);

        return '<script type="text/x-magento-init">' . $script . '</script>';
    }

    private function prepareImagesConfig(array $imagesConfig): array
    {
        $imgKeys = ['thumb', 'img', 'full'];

        foreach ($imagesConfig as $idx => $imgConfig) {
            if (!isset($imgConfig['type']) || ($imgConfig["type"] !== 'image' && $imgConfig["type"] !== 'video')) {
                continue;
            }

            foreach ($imgConfig as $key => $value) {
                if ($key == 'srcset' && is_array($value)) {
                    $imgConfig[$key] = $this->processSrcset($value);
                    continue;
                }

                if (!in_array($key, $imgKeys)) {
                    continue;
                }

                $imgConfig[$key] = $this->getImageWebpUrl($value);
            }

            $imagesConfig[$idx] = $imgConfig;
        }

        return $imagesConfig;
    }

    private function processSrcset(array $srcset): array
    {
        foreach ($srcset as $key => $set) {
            if (!is_string($set)) {
                continue;
            }

            $config = explode(',', $set);

            foreach ($config as $idx => $item) {
                preg_match('/([\S]*)\s+\d+w/s', trim($item), $match);

                if (!isset($match[1])) {
                    continue;
                }

                $config[$idx] = str_replace(
                    $match[1],
                    $this->getImageWebpUrl($match[1]),
                    $item
                );
            }

            $srcset[$key] = implode(',', $config);
        }

        return $srcset;
    }

    private function getImageWebpUrl(string $imageUrl): string
    {
        if (strpos($imageUrl, '.webp') !== false) {
            return $imageUrl;
        }

        preg_match('/\?.*/is', $imageUrl, $query);

        $query = count($query) ? $query[0] : '';
        $value = str_replace($query, '', $imageUrl);

        $absolutePath = $this->configProvider->retrieveImageAbsPath($value);

        if (!$absolutePath) {
            return $imageUrl;
        }

        $image = $this->syncService->ensureFile($absolutePath);

        if ($image && $image->getWebpPath()) {
            $webpPath = $this->configProvider->getAbsolutePath($image->getWebpPath());

            if (!file_exists($webpPath)) {
                $image->setWebpPath(null);
                $this->fileRepository->save($image);

                return $imageUrl;
            }

            $webpUrl = $this->configProvider->getImageUrl($image->getWebpPath());

            $imageUrl = $webpUrl . $query;
        }

        return $imageUrl;
    }

    private function replaceNotoramaCallback(array $match): string
    {
        $config       = SerializeService::decode($match[1]);
        $imagesConfig = SerializeService::decode($config['notorama']['initialImages']);
        $imagesConfig = $this->prepareImagesConfig($imagesConfig);

        $config['notorama']['initialImages'] = SerializeService::encode($imagesConfig);

        $config = SerializeService::encode($config);

        return str_replace($match[1], $config, $match[0]);
    }

    private function replaceBreezeMageInitCallback(array $match): string
    {
        if (
            strpos($match[0], 'data-gallery-role') === false
            || strpos($match[0], 'gallery-placeholder') === false
        ) {
            return $match[0];
        }

        $initConfig = SerializeService::decode($match[1]);

        if (!isset($initConfig['mage/gallery/gallery']['data'])) {
            return $match[0];
        }

        $initConfig['mage/gallery/gallery']['data'] = $this->prepareImagesConfig($initConfig['mage/gallery/gallery']['data']);

        $serialized = SerializeService::encode($initConfig);
        $serialized = str_replace('mage\/gallery\/gallery', 'mage/gallery/gallery', $serialized);

        return str_replace($match[1], $serialized, $match[0]);
    }

    private function replaceBreezeScriptCallback(array $match): string
    {
        $config    = SerializeService::decode($match[1]);
        $configKey = array_keys($config)[0];

        $initConfig = $config[$configKey];

        if (!isset($initConfig['mage/gallery/gallery']['data'])) {
            return $match[0];
        }

        $initConfig['mage/gallery/gallery']['data'] = $this->prepareImagesConfig($initConfig['mage/gallery/gallery']['data']);

        $config[$configKey] = $initConfig;

        $serialized = SerializeService::encode($config);
        $serialized = str_replace('mage\/gallery\/gallery', 'mage/gallery/gallery', $serialized);

        return str_replace($match[1], $serialized, $match[0]);
    }
}
