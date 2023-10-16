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
namespace Bss\SeoAltText\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AfterProductSave
 * @package Bss\SeoAltText\Observer
 */
class AfterProductSave implements ObserverInterface
{

    /**
     * @var \Bss\SeoAltText\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Bss\SeoAltText\Helper\File
     */
    private $fileHelper;
    /**
     * @var \Bss\SeoAltText\Model\ResourceModel\ProductAlbum
     */
    private $productAlbumModel;

    /**
     * AfterProductSave constructor.
     * @param \Bss\SeoAltText\Helper\Data $dataHelper
     * @param \Bss\SeoAltText\Helper\File $fileHelper
     * @param \Bss\SeoAltText\Model\ResourceModel\ProductAlbum $productAlbumModel
     */
    public function __construct(
        \Bss\SeoAltText\Helper\Data $dataHelper,
        \Bss\SeoAltText\Helper\File $fileHelper,
        \Bss\SeoAltText\Model\ResourceModel\ProductAlbum $productAlbumModel
    ) {
        $this->productAlbumModel = $productAlbumModel;
        $this->dataHelper = $dataHelper;
        $this->fileHelper = $fileHelper;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $productObject = $observer->getProduct();
        $fileNameRender = '';

        if (!$this->dataHelper->isEnableModule()) {
            return $this;
        }

        if ((int)$productObject->getData('excluded_alt_text') === 1) {
            return $this;
        }

        $fileTemplate = $this->dataHelper->getFileTemplate();
        if ($fileTemplate) {
            $fileNameRender = $this->dataHelper->convertVar($productObject, $fileTemplate);
            $fileNameRender = $this->dataHelper->createSlugByString($fileNameRender);
        }
        $existingMediaGalleryEntries =  $productObject->getMediaGalleryEntries();
        if (empty($existingMediaGalleryEntries)) {
            return $this;
        }
        foreach ($existingMediaGalleryEntries as $key => $entry) {
            if ($fileNameRender) {
                $fileName = $entry->getFile();
                $fileNameOnly = $this->fileHelper->getImageFile($fileName);
                $fileExtension = $this->fileHelper->getExtensionFromFile($fileNameOnly);
                $fileValueToHandle = $fileNameRender . '.' . $fileExtension;
                $newFilePath = $this->fileHelper->processImageFile($fileName, $fileValueToHandle);
                if ($newFilePath['status'] && $newFilePath['data']['new_path']) {
                    $newPathToSave = $newFilePath['data']['new_path'];
                    $this->productAlbumModel->updateValue($fileName, $newPathToSave);
                }
                $existingMediaGalleryEntries[$key] = $entry;
            }
        }
        return $this;
    }

}
