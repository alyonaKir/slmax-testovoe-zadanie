<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class File
 * @package Bss\SeoAltText\Helper
 */
class File
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $file;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulation;

    /**
     * File constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Magento\Framework\Filesystem\Driver\File $file
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->filesystem = $filesystem;
        $this->emulation = $emulation;
        $this->file = $file;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
    }

    /**
     * @return \Magento\Store\Model\App\Emulation
     */
    public function getEmulation()
    {
        return $this->emulation;
    }

    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getProductFactory()
    {
        return $this->productFactory;
    }

    /**
     * @return \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * @param string $imageFile
     * @return mixed|string
     */
    public function getImageFile($imageFile)
    {
        //Check End Path
        $finalPath = '';
        $imageFileArray = explode('/', $imageFile);
        if (!empty($imageFileArray)) {
            $finalPath = end($imageFileArray);
        }
        return $finalPath;
    }

    /**
     * @param string $fileName
     * @return mixed|string
     */
    public function getExtensionFromFile($fileName)
    {
        if (!$fileName) {
            return '';
        }
        $fileNameArray = explode('.', $fileName);
        $extension = array_pop($fileNameArray);
        return $extension;
    }

    /**
     * @param string $fileName
     * @param string $fileValue
     * @return array
     */
    public function processImageFile($fileName, $fileValue)
    {
        $dataReturn = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        //Get Path of Current Filename
        $fileNameArray = explode('/', $fileName);
        array_pop($fileNameArray);
        $pathFile = implode('/', $fileNameArray);
        $newFileWithPath = $pathFile . '/' . $fileValue;
        //Rename Path
        if ($fileName === $newFileWithPath) {
            $dataReturn['status'] = true;
            $dataReturn['data'] = [
                'new_path' => ''
            ];
            return $dataReturn;
        } else {
            $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $mediaPath = $mediaPath . 'catalog/product';
            $oldPath = $mediaPath . $fileName;
            $pathFileToCheck = $mediaPath . $pathFile;
            try {
                //Check if Exist if add Name with Random String
                $newPathObject = $this->checkPathExist($pathFileToCheck, $fileValue);
                $filePathReturn = $pathFile . '/' . $newPathObject['file_path'];
                $statusRename = $this->file->rename($oldPath, $newPathObject['path']);
                if ($statusRename) {
                    $dataReturn['status'] = true;
                    $dataReturn['data'] = [
                        'new_path' => $filePathReturn
                    ];
                } else {
                    $dataReturn['message'] = __("Can't rename the product Image.");
                }
            } catch (\Exception $exception) {
                $dataReturn['message'] = $exception->getMessage();
            }
            return $dataReturn;
        }
    }

    /**
     * @param string $path
     * @param string $fileName
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function checkPathExist($path, $fileName)
    {
        $dataReturn = [
            'path' => '',
            'file_path' => ''
        ];
        $pathToCheck = $path . '/' . $fileName;
        if ($this->file->isExists($pathToCheck)) {
            $fileNameString = $this->getFileNameFromFile($fileName);
            $fileExtension = $this->getExtensionFromFile($fileName);

            $fileNameAfter = $fileNameString . $this->getRandomString(4);
            $fileAfter = $fileNameAfter . '.' . $fileExtension;
            $fileAfterCheck = $path . '/' . $fileAfter;
            while ($this->file->isExists($fileAfterCheck)) {
                $fileNameAfter = $fileNameString . $this->getRandomString(4);
                $fileAfter = $fileNameAfter . '.' . $fileExtension;
                $fileAfterCheck = $path . '/' . $fileAfter;
            }
            $dataReturn['path'] = $fileAfterCheck;
            $dataReturn['file_path'] = $fileAfter;
            return $dataReturn;
        } else {
            $dataReturn['path'] = $path . '/' . $fileName;
            $dataReturn['file_path'] = $fileName;
            return $dataReturn;
        }
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getFileNameFromFile($fileName)
    {
        if (!$fileName) {
            return '';
        }
        $fileNameArray = explode('.', $fileName);
        array_pop($fileNameArray);
        $fileOriginString = implode('.', $fileNameArray);
        return $fileOriginString;
    }

    /**
     * @param int $number
     * @return string
     */
    public function getRandomString($number = 6) : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $number; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
