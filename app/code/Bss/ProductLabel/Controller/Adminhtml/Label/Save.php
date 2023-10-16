<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 *  @category  BSS
 *  @package   Bss_ProductLabel
 *  @author    Extension Team
 *  @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductLabel\Controller\Adminhtml\Label;

use Bss\ProductLabel\Controller\Adminhtml\Label;
use Bss\ProductLabel\Model\LabelFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Bss\ProductLabel\Helper\SaveObject;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Save
 * @package Bss\ProductLabel\Controller\Adminhtml\Label
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Label
{
    /** Label image Directory */
    const IMG_DIR = 'product_label/';

    protected $saveObjectHelper;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        LabelFactory $labelFactory,
        \Psr\Log\LoggerInterface $logger,
        SaveObject $saveObjectHelper
    ) {
        $this->saveObjectHelper = $saveObjectHelper;
        parent::__construct($context, $coreRegistry, $resultPageFactory, $labelFactory, $logger);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $isPost = $this->getRequest()->getPost();

        if ($isPost) {
            $labelModel = $this->labelFactory->create();

            $beforeImageData = null;
            $beforeProIds = null; // for checking before product conditions
            $beforeImage = null;
            $data = $this->getRequest()->getPostValue();
            $data = $this->prepareData($data);

            $labelId = isset($data['label']['id']) ? $data['label']['id'] : null;
            if ($labelId) {
                $labelModel->load($labelId);
                $beforeImageData = $labelModel->getImageData();
                $beforeProIds = $labelModel->getProductIds();
                $beforeImage = $labelModel->getImage();
            }

            $image_data = json_decode($data['label']['image_data']);
            $startDate = $data['label']['valid_start_date'];
            $endDate = $data['label']['valid_end_date'];

            // checking end date > start date
            if ($this->validateTime($startDate, $endDate, $labelModel->getId())) {
                return $this->validateTime($startDate, $endDate, $labelModel->getId());
            }

            $files = $this->getRequest()->getFiles('image');
            $labelModel->loadPost($data['label']);

            if ($this->validateFile($files, $labelModel->getId())) {
                return $this->validateFile($files, $labelModel->getId());
            }

            $resultImage = $this->canUploadFile($files, $image_data);

            if ($resultImage) {
                //delete old image
                $lableImageOld = $labelModel->getImage();
                $this->checkImage($lableImageOld, $resultImage);
                $labelModel->setData('image', $resultImage);
            }

            $labelImg = $labelModel->getImage();
            $this->canResizeImage($files, $beforeImageData, $beforeImage, $labelImg, $data, $image_data);

            try {
                $labelModel->save();

                // handle save product valid conditions
                $labelModel->handleSaveToProductAttribute();
                // handle clean product attribute
                $labelModel->cleanProductAttribute($beforeProIds);

                return $this->goBack($labelModel->getId());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Please review the form and make corrections'));
            }

            return $resultRedirect->setPath('*/*/edit', ['id' => $labelId]);
        }
    }

    /**
     * @param $files
     * @param $beforeImageData
     * @param $beforeImage
     * @param $labelImg
     * @param $data
     * @param $image_data
     * @throws \Exception
     */
    protected function canResizeImage($files, $beforeImageData, $beforeImage, $labelImg, $data, $image_data)
    {
        // handle if image position/size changed
        if ((empty($files['name'])) && $beforeImageData !=null &&
            $beforeImage == $labelImg && $data['label']['image_data'] != $beforeImageData) {
            $imgUrl = $this->saveObjectHelper->getFilesystem()->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath() . $labelImg;
            $this->resizeImage($imgUrl, $image_data->widthOrigin, $image_data->heightOrigin);
        }
    }

    /**
     * Check Label Image
     *
     * @param $lableImageOld
     * @param $resultImage
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function checkImage($lableImageOld, $resultImage)
    {
        if ($lableImageOld && $lableImageOld != $resultImage) {
            $this->deleteImage($lableImageOld);
        }

    }

    /**
     * Prepares specific data
     *
     * @param $data
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function prepareData($data)
    {
        //handle convert groups array to string
        if (isset($data['label']['customer_groups'])) {
            $groups_filter = array_filter(array_map('trim', $data['label']['customer_groups']), 'strlen');
            $data['label']['customer_groups'] = implode(',', $groups_filter);
        }
        if (isset($data['label']['store_views'])) {
            $store_filter = array_filter(array_map('trim', $data['label']['store_views']), 'strlen');
            $data['label']['store_views'] = implode(',', $store_filter);
        }

        //handle insert conditions data to label array
        if (isset($data['rule']['conditions'])) {
            $data['label']['conditions'] = $data['rule']['conditions'];
        }
        unset($data['rule']['conditions']);

        // fix for before data of older version
        if (isset($data['parameters']['conditions'])) {
            $data['label']['conditions'] = $data['label']['conditions'] + $data['parameters']['conditions'];
        }

        //handle delete image & set image in case of editing
        if (isset($data['label']['image']) && isset($data['label']['image']['value'])) {
            if (isset($data['label']['image']['delete'])) {
                $this->deleteImage($data['label']['image']['value']);
                $data['label']['image'] = null;
            } elseif (isset($data['label']['image']['value'])) {
                $data['label']['image'] = $data['label']['image']['value'];
            } else {
                $data['label']['image'] = null;
            }
        }

        return $data;
    }

    /**
     * Handle upload and resize
     * @param $width
     * @param $height
     * @return null|string
     * @throws \Exception
     */
    protected function uploadImage($width, $height)
    {
        $imageUrl = null;
        try {
            $uploader = $this->saveObjectHelper->getFileUploaderFactory()->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $path = $this->saveObjectHelper->getFilesystem()->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath(self::IMG_DIR);
            $result = $uploader->save($path);
            $imageUrl = self::IMG_DIR . $result['file'];
            $absoluteImageUrl = $path . $result['file'];

            $this->resizeImage($absoluteImageUrl, $width, $height);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Image can\'t be uploaded'));
            $this->logger->critical($e);
        }

        return $imageUrl;
    }

    /**
     * Handle resize image
     * @param $absoluteImageUrl
     * @param $width
     * @param $height
     * @throws \Exception
     */
    protected function resizeImage($absoluteImageUrl, $width = 250, $height = null)
    {
        $imageResize = $this->saveObjectHelper->getImageFactory()->create();
        try {
            $imageResize->open($absoluteImageUrl);
            $imageResize->constrainOnly(false);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio(true);
            $imageResize->resize($width, $height);
            $imageResize->save($absoluteImageUrl);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Handle delete image
     *
     * @param $image
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function deleteImage($image)
    {
        if ($image == null) {
            return;
        }

        $uploadDir = $this->saveObjectHelper->getFilesystem()
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        if ($this->saveObjectHelper->getFile()->isExists($uploadDir . $image)) {
            try {
                $this->saveObjectHelper->getFile()->deleteFile($uploadDir . $image);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Image can\'t be deleted'));
                $this->logger->critical($e);
            }
        }
    }

    /**
     * @param $start
     * @param $end
     * @param $labelId
     * @return bool|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    protected function validateTime($start, $end, $labelId)
    {
        try {
            if (!empty($start) && !empty($end)) {
                $st = $this->saveObjectHelper->getDate()->date('Y-m-d H:i:s', $start);
                $et = $this->saveObjectHelper->getDate()->date('Y-m-d H:i:s', $end);
                if ($st >= $et) {
                    $this->messageManager->addErrorMessage(__('Valid End Date must follow Start Date.'));
                    return $this->returnResult('*/*/edit', ['id' => $labelId, '_current' => true], ['error' => true]);
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Please input valid date'));
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * @param $files
     * @param $labelId
     * @param $image_data
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json|null|string
     * @throws \Exception
     */
    protected function validateFile($files, $labelId)
    {
        if (!empty($files['name'])) {
            if (empty($files['tmp_name']) || $files['size'] == 0) {
                $this->messageManager->addErrorMessage(__('That image can not upload, please choose another'));
                return $this->returnResult('*/*/edit', ['id' => $labelId, '_current' => true], ['error' => true]);
            }
        }
        return false;
    }

    protected function canUploadFile($files, $image_data)
    {
        if (!empty($files['name'])) {
            if (!empty($image_data)) {
                $resultImage = $this->uploadImage($image_data->widthOrigin, $image_data->heightOrigin);
                return $resultImage;
            }
        }

        return false;
    }
    /**
     * @param $labelId
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    protected function goBack($labelId)
    {
        $this->messageManager->addSuccessMessage(__('The item has been saved.'));
        if ($this->getRequest()->getParam('back', false)) {
            // Display success message
            return $this->returnResult('*/*/edit', ['id' => $labelId, '_current' => true], ['error' => false]);
        }
        // Go to grid page
        return $this->returnResult('*/*/', [], ['error' => false]);
    }

    /**
     * @param string $path
     * @param array $params
     * @param array $response
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Backend\Model\View\Result\Redirect
     */
    private function returnResult($path = '', array $params = [], array $response = [])
    {
        if ($this->isAjax()) {
            $layout = $this->saveObjectHelper->getLayoutFactory()->create();
            $layout->initMessages();

            $response['messages'] = [$layout->getMessagesBlock()->getGroupedHtml()];
            $response['params'] = $params;
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path, $params);
    }

    /**
     * @return bool
     */
    private function isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }
}