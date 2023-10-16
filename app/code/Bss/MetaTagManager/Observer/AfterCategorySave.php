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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AfterCategorySave
 * @package Bss\MetaTagManager\Observer
 */
class AfterCategorySave implements ObserverInterface
{
    /**
     * @var \Bss\MetaTagManager\Model\MetaTemplateFactory
     */
    private $metaTemplateFactory;
    /**
     * @var \Bss\MetaTagManager\Helper\ProcessMetaTemplate
     */
    private $processMetaTemplate;

    /**
     * AfterCategorySave constructor.
     * @param \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
     * @param \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
     */
    public function __construct(
        \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory,
        \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
    ) {
        $this->processMetaTemplate = $processMetaTemplate;
        $this->metaTemplateFactory = $metaTemplateFactory;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $categoryObject = $observer->getEvent()->getCategory();
        if (!$categoryObject->getId()) {
            return $this;
        }

        $parentCategories = $categoryObject->getParentIds();
        if (empty($parentCategories)) {
            $parentCategories = [];
        }
        $parentCategories[] = $categoryObject->getId();
        $collection = $this->metaTemplateFactory->create()
            ->getCollection()
            ->addFieldToFilter('meta_type', 'category')
            ->addFieldToFilter('status', '1');
        if ($collection->getSize()) {
            $finalTemplate = [];
            $maxPriority = 0;
            foreach ($collection as $metaObject) {
                $currentStoreView = $categoryObject->getStoreId();
                $statusCategoryTemplate = $this->isCategoryTemplate(
                    $metaObject,
                    $parentCategories,
                    $currentStoreView,
                    $categoryObject->getId()
                );
                if (!$statusCategoryTemplate) {
                    continue;
                }
                //HandleData
                $priority = $metaObject->getPriority();
                if ((int)$priority >= $maxPriority) {
                    $finalTemplate = $metaObject;
                    $maxPriority = (int)$priority;
                }
            }

            $excludedMetaTemplate = $categoryObject->getData('excluded_meta_template');
            if ($excludedMetaTemplate !== '1' && !empty($finalTemplate)) {
                $this->processMetaTemplate->handleCategoryMeta($categoryObject, $finalTemplate);
            }
            return $this;
        } else {
            return $this;
        }
    }

    /**
     * @param object $metaObject
     * @param array $parentCategories
     * @param string $storeId
     * @param string $categoryId
     * @return bool
     */
    public function isCategoryTemplate($metaObject, $parentCategories, $storeId, $categoryId)
    {
        $templateCategories = $metaObject->getCategory();
        $storeTemplate = $metaObject->getStore();
        if ($templateCategories && $storeTemplate) {
            $templateCategoryObject = explode(',', $templateCategories);
            $storeTemplateObject = explode(',', $storeTemplate);
            if ((int)$storeId && !in_array($storeId, $storeTemplateObject)) {
                return false;
            }

            $statusReturn = $this->isStatusTemplate(
                $templateCategoryObject,
                $parentCategories,
                $metaObject,
                $categoryId
            );
            return $statusReturn;
        } else {
            return false;
        }
    }

    /**
     * @param array $templateCategoryObject
     * @param array $parentCategories
     * @param object $metaObject
     * @param string $categoryId
     * @return bool
     */
    public function isStatusTemplate($templateCategoryObject, $parentCategories, $metaObject, $categoryId)
    {
        $statusReturn = false;
        foreach ($templateCategoryObject as $categoryTemplateId) {
            if (in_array($categoryTemplateId, $parentCategories) && (int)$metaObject->getUseSub()) {
                $statusReturn = true;
            }
            if ((int)$categoryId === (int)$categoryTemplateId && (int)$metaObject->getUseSub() === 0) {
                $statusReturn = true;
            }
        }
        return $statusReturn;
    }
}
