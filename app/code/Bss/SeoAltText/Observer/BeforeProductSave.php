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

use Bss\MetaTagManager\Controller\Adminhtml\Generate\GenerateMeta;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class BeforeProductSave
 * @package Bss\SeoAltText\Observer
 */
class BeforeProductSave implements ObserverInterface
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
     * BeforeProductSave constructor.
     * @param \Bss\SeoAltText\Helper\Data $dataHelper
     * @param \Bss\SeoAltText\Helper\File $fileHelper
     */
    public function __construct(
        \Bss\SeoAltText\Helper\Data $dataHelper,
        \Bss\SeoAltText\Helper\File $fileHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->fileHelper = $fileHelper;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute(EventObserver $observer)
    {
        $productObject = $observer->getProduct();
        $altRender = '';

        if (!$this->dataHelper->isEnableModule()) {
            return $this;
        }
        if ((int)$productObject->getData('excluded_alt_text') === 1) {
            return $this;
        }
        //Process Data
        $altTemplate = $this->dataHelper->getAltTemplate();
        if ($altTemplate) {
            $altRender = $this->dataHelper->convertVar($productObject, $altTemplate);
        }
        if ($altRender) {
            $mediaGallery = $productObject->getData('media_gallery');
            if (isset($mediaGallery['images']) && !empty($mediaGallery['images'])) {
                foreach ($mediaGallery['images'] as $key => $galleryItem) {
                    $mediaGallery['images'][$key]['label'] = $altRender;
                }
            }
            $productObject->setData('media_gallery', $mediaGallery);
        }
        return $this;
    }

}
