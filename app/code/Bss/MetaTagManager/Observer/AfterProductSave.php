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
 * Class AfterProductSave
 * @package Bss\MetaTagManager\Observer
 */
class AfterProductSave implements ObserverInterface
{
    /**
     * @var \Bss\MetaTagManager\Model\MetaTemplateFactory
     */
    private $metaTemplateFactory;
    /**
     * @var \Bss\MetaTagManager\Model\RuleFactory
     */
    private $ruleFactory;
    /**
     * @var \Bss\MetaTagManager\Helper\ProcessMetaTemplate
     */
    private $processMetaTemplate;

    /**
     * AfterProductSave constructor.
     * @param \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
     * @param \Bss\MetaTagManager\Model\RuleFactory $ruleFactory
     * @param \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
     */
    public function __construct(
        \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory,
        \Bss\MetaTagManager\Model\RuleFactory $ruleFactory,
        \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
    ) {
        $this->processMetaTemplate = $processMetaTemplate;
        $this->ruleFactory = $ruleFactory;
        $this->metaTemplateFactory = $metaTemplateFactory;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $productObject = $observer->getProduct();
        if (!$productObject->getId()) {
            return $this;
        }
        $collection = $this->metaTemplateFactory->create()
            ->getCollection()
            ->addFieldToFilter('meta_type', 'product')
            ->addFieldToFilter('status', '1');
        if ($collection) {
            $productCheckObject = [];
            foreach ($collection as $metaObject) {
                $metaData = $metaObject->getData();
                //HandleData
                $priority = $metaData['priority'];
                //Set Data
                $modelRule = $this->ruleFactory->create();
                $modelRule->setMetaData($metaData);
                $statusValidate = $modelRule->validateProductConditions($productObject);
                if ($statusValidate) {
                    $dataToAdd = [
                        'meta_object' => $metaObject,
                        'priority' => $priority
                    ];
                    $productCheckObject[] = $dataToAdd;
                }

            }
            //Check in ProductCheckObject
            if (!empty($productCheckObject)) {
                $maxPriority = 0;
                $metaObjectFinal = [];
                foreach ($productCheckObject as $item) {
                    $priorityItem = $item['priority'];
                    if ((int)$priorityItem >= $maxPriority) {
                        $maxPriority = (int)$priorityItem;
                        $metaObjectFinal = $item['meta_object'];
                    }
                }
                $this->generateProduct($productObject, $metaObjectFinal);
            }
        }
        return $this;
    }

    /**
     * @param object $product
     * @param object $metaTemplate
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function generateProduct($product, $metaTemplate)
    {
        $currentStoreId = $product->getStoreId();
        //Check Product Template

        $storeViewTemplate = $metaTemplate->getStore();
        $storeViewTemplateArray = explode(',', $storeViewTemplate);

        if ($currentStoreId && !in_array($currentStoreId, $storeViewTemplateArray)) {
            return $this;
        }
        //Check Store view Template with Current StoreView
        $productExcludeTemplateStore = $product->getData('excluded_meta_template');
        if ($productExcludeTemplateStore !== '1') {
            $this->processMetaTemplate->processProductMeta($product, $metaTemplate);
        }
        return $this;
    }

}
