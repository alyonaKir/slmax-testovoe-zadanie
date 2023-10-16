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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Repository\FileRepository;
use Mirasvit\OptimizeImage\Service\ResponsiveImageService;

class ConfigProvider
{
    const CMD_CONVERT_RGB      = 'convert -colorspace RGB "%s" "%s"';
    const CMD_PROCESS_WEBP     = 'cwebp -q %s "%s" -o "%s"';
    const CMD_PROCESS_GIF2WEBP = 'gif2webp -q %s "%s" -o "%s"';
    const CMD_PROCESS_PNG      = 'optipng "%s"';
    const CMD_PROCESS_GIF      = 'gifsicle "%s" -o "%s"';
    const CMD_PROCESS_JPG      = 'jpegoptim --all-progressive --strip-xmp --strip-com --strip-exif --strip-iptc "%s"';

    const WEBP_SUFFIX    = '.webp';
    const CONVERT_SUFFIX = '.mst.conv';
    const BACKUP_SUFFIX  = "_mst_ORIG";
    const TMP_SUFFIX     = "_mst_TMP";

    const STRATEGY_FILESYSTEM = 'file';
    const STRATEGY_WEBPAGES   = 'web';

    const OPTIMIZE_DIR_NAME  = 'iopt';
    const RESIZED_DIR_PREFIX = 'resize-';

    private $fs;

    private $scopeConfig;

    private $request;

    private $storeManager;

    private $fileRepository;

    public function __construct(
        Filesystem            $fs,
        ScopeConfigInterface  $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface      $request,
        FileRepository        $fileRepository
    ) {
        $this->fs             = $fs;
        $this->storeManager   = $storeManager;
        $this->scopeConfig    = $scopeConfig;
        $this->request        = $request;
        $this->fileRepository = $fileRepository;
    }

    public function isWebpEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'mst_optimize/optimize_image/is_webp',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isAllowedFileExtension(string $extension): bool
    {
        return in_array($extension, ['png', 'gif', 'jpg', 'jpeg']);
    }

    public function isLazyEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'mst_optimize/optimize_image/image_lazy_load/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAbsolutePath(string $relativePath): string
    {
        $abs = $this->fs->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        $abs .= $relativePath;

        if (!file_exists($abs)) {
            $this->deleteRemovedFile($abs);
        }

        return $abs;
    }

    public function getRelativePath(string $absolutePath): string
    {
        $abs = $this->fs->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();

        return str_replace($abs, '', $absolutePath);
    }

    public function getOptPathFromRelativePath(string $relativePath): string
    {
        if (strpos($relativePath, 'pub/media/' . self::OPTIMIZE_DIR_NAME) !== false) {
            return $relativePath;
        }

        return preg_replace('@^pub(/media)?@', 'pub/media/' . self::OPTIMIZE_DIR_NAME, $relativePath);
    }

    public function getResizedPath(FileInterface $file, string $path, string $identifier): string
    {
        if (preg_match('@/' . self::RESIZED_DIR_PREFIX . $identifier . '/@', $path)) {
            return $path;
        }

        return substr($path, 0, strrpos($path, $file->getBasename())) . $this->getResizeDirName($identifier) . $file->getBasename();
    }

    public function getResizeDirName(string $identifier): string
    {
        return self::RESIZED_DIR_PREFIX . $identifier . '/';
    }

    public function retrieveImageAbsPath(string $url): ?string
    {
        return $this->retrieveMediaAbsPath($url)
            ? : $this->retrieveStaticAbsPath($url)
                ? : $this->retrieveOtherAbsPath($url)
                    ? : null;
    }

    public function isFileExist(string $relativePath): bool
    {
        return $this->isExistInMediaDir($relativePath) || $this->isExistInPubDir($relativePath);
    }

    public function getFileExtension(string $absolutePath): string
    {
        $pathInfo = pathinfo($absolutePath);

        return isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
    }

    public function isWebpException(string $img): bool
    {
        if (strpos($img, 'lazyOwl') !== false
            || strpos($img, 'owl-lazy') !== false
            || strpos($img, 'swiper-lazy') !== false
            || strpos($img, 'mst-no-webp') !== false
            || strpos($img, 'loader.gif') !== false
        ) {
            return true;
        }

        return false;
    }

    public function getCompressionLevel(): int
    {
        $compression = $this->scopeConfig->getValue('mst_optimize/optimize_image/compression');

        return $compression ? (int)$compression : 100;
    }

    public function isDebug(): bool
    {
        return $this->request->getParam('debug') == 'optimize-image';
    }

    public function getStrategy(): string
    {
        return $this->scopeConfig->getValue('mst_optimize/optimize_image/strategy') ? : self::STRATEGY_FILESYSTEM;
    }

    public function isFilesystemStrategy(): bool
    {
        return $this->getStrategy() == self::STRATEGY_FILESYSTEM;
    }

    /**
     * @return array|false
     */
    public function getResponsiveImages()
    {
        $conf = $this->scopeConfig->getValue('mst_optimize/optimize_image/responsive/image');
        $conf = SerializeService::decode($conf);

        if (!is_array($conf) && is_object($conf)) {
            $conf = (array)$conf;
            foreach ($conf as $key => $value) {
                if (is_object($value)) {
                    $conf[$key] = (array)$value;
                }
            }
        }

        if (is_array($conf)) {
            foreach ($conf as $confKey => $confData) {
                if (
                    !$confData['file']
                    || !(int)$confData[ResponsiveImageService::MOBILE_IDENTIFIER]
                ) {
                    unset($conf[$confKey]);
                }
            }
        }

        return $conf;
    }

    public function getResponsiveImageConfigByFileName(string $file): ?array
    {
        $resposive = $this->getResponsiveImages();

        if (!is_array($resposive)) {
            return null;
        }

        foreach ($resposive as $image) {
            if (strpos($file, $image['file']) !== false) {
                return $image;
            }
        }

        return null;
    }

    public function getImageUrl(string $relativePath): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . preg_replace('@^pub(/media)?/@', '', $relativePath);
    }

    private function retrieveMediaAbsPath(string $url): ?string
    {
        $baseUrl  = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $mediaDir = $this->fs->getDirectoryread(DirectoryList::MEDIA);

        if (strpos($url, $mediaUrl) === false && substr($url, 0, 1) != '/') {
            return null;
        }

        if (substr($url, 0, 1) == '/') {
            $mediaRelativePath = str_replace($baseUrl, '', $mediaUrl);
            $url = str_replace($mediaRelativePath, '', $url);
        }

        $path = str_replace($mediaUrl, '', $url);

        if (!$mediaDir->isExist($path)) {
            $this->deleteRemovedFile($mediaDir->getAbsolutePath($path));

            return null;
        }

        return $mediaDir->getAbsolutePath($path);
    }

    private function retrieveStaticAbsPath(string $url): ?string
    {
        $rootUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $rootDir = $this->fs->getDirectoryRead(DirectoryList::ROOT);

        if (strpos($url, $rootUrl) === false) {
            return null;
        }

        $path = str_replace($rootUrl, '', $url);

        if (!preg_match('@/version[^/]*/@', $path)) {
            return null;
        }

        $path = preg_replace('@/version[^/]*/@', '/', $path);

        if (strpos('pub/', $path) !== 0) {
            $path = 'pub/' . $path;
        }

        if (!$rootDir->isExist($path)) {
            $this->deleteRemovedFile($rootDir->getAbsolutePath($path));

            return null;
        }

        return $rootDir->getAbsolutePath($path);
    }

    private function retrieveOtherAbsPath(string $url): ?string
    {
        $rootUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $rootDir = $this->fs->getDirectoryRead(DirectoryList::ROOT);

        if (strpos($url, $rootUrl) === false) {
            return null;
        }

        $path = str_replace($rootUrl, '', $url);

        if (strpos($path, 'pub/') !== 0) {
            $path = 'pub/' . $path;
        }

        if (!$rootDir->isExist($path)) {
            $this->deleteRemovedFile($rootDir->getAbsolutePath($path));

            return null;
        }

        return $rootDir->getAbsolutePath($path);
    }

    private function isExistInMediaDir(string $path): bool
    {
        $mediaDir = $this->fs->getDirectoryread(DirectoryList::MEDIA);

        $path = str_replace('pub/media/', '', $path);

        return $mediaDir->isExist($path);
    }

    private function isExistInPubDir(string $path): bool
    {
        $rootDir = $this->fs->getDirectoryRead(DirectoryList::ROOT);

        if (strpos('pub/', $path) !== 0) {
            $path = 'pub/' . $path;
        }

        return $rootDir->isExist($path);
    }

    private function deleteRemovedFile(string $absolutePath)
    {
        $relativePath = $this->getRelativePath($absolutePath);
        $file         = $this->fileRepository->getByRelativePath($relativePath);

        if (!$file) {
            return;
        }

        $this->fileRepository->delete($file);
    }
}
