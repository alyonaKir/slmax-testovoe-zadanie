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
namespace Bss\SeoAltText\Controller\Adminhtml\Process;

use Bss\SeoAltText\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Area;

/**
 * Class Product
 * @package Bss\SeoAltText\Controller\Adminhtml\Process
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Product extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var \Bss\SeoAltText\Model\ResourceModel\ProductAlbum
     */
    private $productAlbumModel;
    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var \Bss\SeoAltText\Helper\File
     */
    private $fileHelper;

    /**
     * Product constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Bss\SeoAltText\Model\ResourceModel\ProductAlbum $productAlbumModel
     * @param Data $dataHelper
     * @param \Bss\SeoAltText\Helper\File $fileHelper
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Bss\SeoAltText\Model\ResourceModel\ProductAlbum $productAlbumModel,
        \Bss\SeoAltText\Helper\Data $dataHelper,
        \Bss\SeoAltText\Helper\File $fileHelper,
        Context $context
    ) {
        $this->dataHelper = $dataHelper;
        $this->productAlbumModel = $productAlbumModel;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileHelper = $fileHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $dataReturn = [
            "status" => false,
            "data" => [],
            "message" => ""
        ];
        if ($this->getRequest()->isAjax()) {
            $dataReturn['status'] = true;
            $productId = $this->getRequest()->getPost('product_id');
            $valueId = $this->getRequest()->getPost('value_id');
            $labelValue = $this->getRequest()->getPost('label');
            $fileValue = $this->getRequest()->getPost('file_name');
            $storeValue = $this->getRequest()->getPost('store');
            $typeProcess = $this->getRequest()->getPost('type');
            //Validate Data
            $statusValidate = $this->validateData($productId, $valueId, $fileValue, $typeProcess);
            if ($statusValidate['status']) {
                $changeProductImageObject = $this->changeProductLabel($productId, $labelValue, $fileValue, $valueId, $typeProcess, $storeValue);
                $dataReturn = $changeProductImageObject;
            } else {
                $dataReturn = $statusValidate;
            }
        } else {
            $dataReturn['message'] = __('Request is not valid.');
        }

        $result->setData($dataReturn);
        return $result;
    }

    /**
     * @param string $productId
     * @param string $valueId
     * @param string $fileValue
     * @param string $type
     * @return array
     */
    public function validateData($productId, $valueId, $fileValue, $type)
    {
        $dataReturn = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        if (!$productId || !is_numeric($productId)) {
            $dataReturn['message'] = __('Product ID is not valid.');
            return $dataReturn;
        }
        if ($type === 'generate') {
            $dataReturn['status'] = true;
            return $dataReturn;
        }
        if (!$valueId || !is_numeric($valueId)) {
            $dataReturn['message'] = __('Value ID is not valid.');
            return $dataReturn;
        }
        if (!$fileValue) {
            $dataReturn['message'] = __('Filename is not valid.');
            return $dataReturn;
        } else {
            $fileName = $this->fileHelper->getFileNameFromFile($fileValue);
            if (preg_match('/^[a-zA-Z0-9_\-.@()]+$/', $fileName)) {
                //Check Extension
                $extension = $this->fileHelper->getExtensionFromFile($fileValue);
                $allowedImage = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
                if (in_array($extension, $allowedImage)) {
                    $dataReturn['status'] = true;
                } else {
                    $dataReturn['message'] = __('Filename is not valid.');
                }
                return $dataReturn;
            } else {
                $dataReturn['message'] = __('Filename is not valid.');
                return $dataReturn;
            }
        }
    }

    /**
     * @param string $productId
     * @param string $labelValue
     * @param string $fileValue
     * @param null $valueId
     * @param null $typeProcess
     * @param null $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeProductLabel(
        $productId,
        $labelValue,
        $fileValue,
        $valueId = null,
        $typeProcess = null,
        $storeId = null
    ) {
        $dataReturn = [
            'status' => false,
            'message' => '',
            'data' => []
        ];

        if (!$storeId) {
            $storeId = 0;
        }
        $productObject = $this->fileHelper->getProductFactory()->create()->setStoreId((int)$storeId)->load($productId);


        $altRender = '';
        $fileNameRender = '';
        if ($typeProcess === 'generate') {
            $isExcludedAltText = $productObject->getData('excluded_alt_text');
            if ((int)$isExcludedAltText === 1) {
                $dataReturn['type_error'] = 'excluded';
                return $dataReturn;
            }
            //Process Data
            $altTemplate = $this->dataHelper->getAltTemplate();
            if ($altTemplate) {
                $altRender = $this->dataHelper->convertVar($productObject, $altTemplate);
            }

            $fileTemplate = $this->dataHelper->getFileTemplate();
            if ($fileTemplate) {
                $fileNameRender = $this->dataHelper->convertVar($productObject, $fileTemplate);
                $fileNameRender = $this->dataHelper->createSlugByString($fileNameRender);
            }
        }

        $this->processChangeImageLabel($productObject, $valueId, $typeProcess, $labelValue, $altRender);
        $this->processChangeImageFilename(
            $productObject,
            $valueId,
            $typeProcess,
            $fileValue,
            $fileNameRender
        );

        $dataReturn['status'] = true;
        $dataReturn['message'] = __('Save image successfully!');
        return $dataReturn;
    }

    /**
     * @param object $productObject
     * @param string $valueId
     * @param string $typeProcess
     * @param string $labelValue
     * @param string $altRender
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function processChangeImageLabel($productObject, $valueId, $typeProcess, $labelValue, $altRender)
    {
        //Set label for Product
        $mediaGallery = $productObject->getData('media_gallery');
        if (isset($mediaGallery['images']) && !empty($mediaGallery['images'])) {
            foreach ($mediaGallery['images'] as $key => $galleryItem) {
                $valueIdImage = $galleryItem['value_id'];
                if ((int)$valueIdImage === (int)$valueId && $typeProcess !== 'generate') {
                    $mediaGallery['images'][$key]['label'] = $labelValue;
                }

                if ($typeProcess === 'generate' && $altRender) {
                    $mediaGallery['images'][$key]['label'] = $altRender;
                }
            }
        }

        $productObject->setData('media_gallery', $mediaGallery);
        $productObject->setData('excluded_alt_text', "1");
        $this->fileHelper->getEmulation()->startEnvironmentEmulation($productObject->getStoreId(), Area::AREA_ADMINHTML);
        $this->fileHelper->getProductRepository()->save($productObject);
        $this->fileHelper->getEmulation()->stopEnvironmentEmulation();
        return $this;
    }

    /**
     * @param object $productObject
     * @param string $valueId
     * @param string $typeProcess
     * @param string $fileValue
     * @param string $fileNameRender
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processChangeImageFilename(
        $productObject,
        $valueId,
        $typeProcess,
        $fileValue,
        $fileNameRender
    ) {
        $existingMediaGalleryEntries = $productObject->getMediaGalleryEntries();

        //Rename Image
        foreach ($existingMediaGalleryEntries as $entry) {
            $valueIdImage = $entry->getId();
            if ((int)$valueIdImage === (int)$valueId && $typeProcess !== 'generate') {
                $fileName = $entry->getFile();
                $newFilePath = $this->fileHelper->processImageFile($fileName, $fileValue);
                if ($newFilePath['status'] && $newFilePath['data']['new_path']) {
                    $newPathToSave = $newFilePath['data']['new_path'];
                    $this->productAlbumModel->updateValue($fileName, $newPathToSave);
                    $dataReturn['data']['image_url'] = $this->dataHelper->getImageUrl($newPathToSave);
                    $dataReturn['data']['image_name'] = $this->fileHelper->getImageFile($newPathToSave);
                }
            }
            $this->processChangeFilenameGenerate($typeProcess, $fileNameRender, $entry);
        }
        return $this;
    }

    /**
     * @param string $typeProcess
     * @param string $fileNameRender
     * @param object $entry
     * @return $this
     */
    public function processChangeFilenameGenerate($typeProcess, $fileNameRender, $entry)
    {
        if ($typeProcess === 'generate') {
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
            }
        }
        return $this;
    }
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_SeoAltText::seo_alt_text');
    }
}
