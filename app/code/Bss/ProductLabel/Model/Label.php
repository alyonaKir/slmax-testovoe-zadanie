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
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Model;

use Magento\Rule\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Model\ResourceModel\Iterator as ResourceModelIterator;
use Bss\ProductLabel\Model\ResourceModel\Label as ResourceModelLabel;

/**
 * Class Label
 * @package Bss\ProductLabel\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Label extends AbstractModel
{
    /**
     * @var array
     */
    public $productIds = [];

    /**
     * @var \Bss\ProductLabel\Helper\ModelLabel
     */
    public $helperModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $_localeDate;
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ResourceModelLabel::class);
    }

    /**
     * Label constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Bss\ProductLabel\Helper\ModelLabel $helperModel
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Bss\ProductLabel\Helper\ModelLabel $helperModel,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperModel = $helperModel;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }
    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    public function getActionsInstance()
    {
        return $this->helperModel->getCondProdCombineF()->create();
    }
    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->helperModel->getCondCombineFactory()->create();
    }

    /**
     * Get array of product ids which are matched by rule
     * @throws \Exception
     */
    public function handleSaveToProductAttribute()
    {
        $condSer = json_decode($this->getConditionsSerialized());
        if (!$this->helperModel->getHelperData()->compareCurrentVersion('2.2.0')) {
            $condSer = unserialize($this->getConditionsSerialized());
        }
        if (is_array($condSer)) {
            $condSer = (object)$condSer;
        }

        if (isset($condSer->conditions) && !empty($condSer->conditions)) {
            $productCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
                ProductCollection::class
            );
            $productFactory = \Magento\Framework\App\ObjectManager::getInstance()->create(
                ProductFactory::class
            );
            $this->setCollectedAttributes([]);
            $this->getConditions()->collectValidatedAttributes($productCollection);
            \Magento\Framework\App\ObjectManager::getInstance()->create(
                ResourceModelIterator::class
            )->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $productFactory->create()
                ]
            );
        } elseif (empty($condSer->conditions) && !empty($this->getProductIds())) {
            $this->cleanProductAttribute($this->getProductIds());
        }

        $this->setProductIds(json_encode($this->productIds));
        $this->save();
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->getWebsitesMap();
        foreach ($websites as $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if ($this->getConditions()->validate($product)) {
                $this->addLabelDataToProductAttribute($product);
            }
        }
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function getWebsitesMap()
    {
        $map = [];
        $websites = $this->helperModel->getStoreManager()->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isAddPids
     */
    protected function addLabelDataToProductAttribute($product, $isAddPids = true)
    {
        $labelData = $product->getResource()->getAttributeRawValue(
            $product->getId(),
            'label_data',
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
        );

        //apply conditions here
        if (empty($labelData)) {
            $labelDataArr = [];
        } else {
            $labelDataArr = json_decode($labelData);
        }

        //check duplicate
        $key = false;
        if (!empty($labelDataArr)) {
            $key = array_search($this->getId(), array_column($labelDataArr, 'id'));
        }
        $is_label_valid = $this->checkLabelConditions();
        $data = $this->getData();
        if (isset($data['product_ids'])) {
            unset($data['product_ids']);
        }
        if (is_int($key) != false) {
            if ($is_label_valid) {
                $labelDataArr[$key] = $data;
            } else {
                unset($labelDataArr[$key]);
                $labelDataArr = array_values($labelDataArr);
            }
        } else {
            if ($is_label_valid) {
                $labelDataArr[] = $data;
            }
        }

        $product->setLabelData(json_encode($labelDataArr));
        $product->getResource()->saveAttribute($product, 'label_data');

        if ($isAddPids) {
            $this->productIds[] = $product->getId();
        }
    }

    /**
     * Check label is allow to active
     * @return bool
     */
    private function checkLabelConditions()
    {
        if (!$this->getActive() || empty($this->getImage() || empty($this->getImageData()))
            || !$this->checkValidEndDate($this->getValidEndDate())) {
            return false;
        }

        return true;
    }

    /**
     * Check label valid end date
     * @param string $endDate
     * @return bool
     */
    private function checkValidEndDate($endDate)
    {
        if (!empty($endDate)) {
            $dateTimeZone = $this->_localeDate->date()->format('Y-m-d H:i:s');
            $currentTime = strtotime($dateTimeZone);
            //check $endDate > $current_time
            if ($currentTime > strtotime($endDate)) {
                return false;
            }
        };

        return true;
    }

    /**
     * Clean product attribute when product is no longer applied
     * @param string $beforeProIds
     * @throws \Exception
     */
    public function cleanProductAttribute($beforeProIds)
    {
        $beforeProIds = json_decode($beforeProIds);
        if (empty($beforeProIds)) {
            return;
        }

        $diff = array_diff($beforeProIds, json_decode($this->getProductIds()));

        $this->cleanProductAttributes($diff);
    }

    /**
     * @param array $productIds
     * @throws \Exception
     */
    private function cleanProductAttributes($productIds)
    {
        if (!empty($productIds)) {
            foreach ($productIds as $pid) {
                // load product and delete attribute 'label_data'
                $product = $this->getProduct($pid);
                if ($product) {
                    $labelData = $product->getResource()->getAttributeRawValue(
                        $product->getId(),
                        'label_data',
                        \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
                    );

                    if (!empty($labelData)) {
                        $labelDataArr = json_decode($labelData);
                        $key = array_search($this->getId(), array_column($labelDataArr, 'id'));

                        if (is_int($key) != false) {
                            unset($labelDataArr[$key]); //maybe there's no index 0, it can be convert to object
                            $labelDataArr = array_values($labelDataArr);

                            $this->helperModel->getProductAction()->updateAttributes(
                                [$pid],
                                ['label_data' => json_encode($labelDataArr)],
                                null
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param int $pid
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($pid)
    {
        $product = $this->helperModel->getProductFactory()->create()->load($pid);
        return $product;
    }

    /**
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        $this->cleanProductAttributes(json_decode($this->getProductIds()));
        return parent::beforeDelete();
    }
}