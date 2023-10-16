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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Model\Config\Backend;

/**
 * Class ProcessCategory
 * @package Bss\HtmlSiteMap\Model\Config\Backend
 */
class ProcessProduct extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * ProcessProduct constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->productResource = $productResource;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }
    /**
     * @return \Magento\Config\Model\Config\Backend\Serialized $this
     * @throws \Exception
     */
    public function beforeSave()
    {
        /* @var array $value */
        $value = $this->getValue();
        $oldValue = $this->getOldValue();
        $storeId = $this->getScopeId();
        if ($oldValue) {
            $oldProductArray = explode(',', $oldValue);
        } else {
            $oldProductArray = [];
        }

        if ($value) {
            $newValueArray = explode(',', $value);
        } else {
            $newValueArray = [];
        }

        if ($value !== $oldValue) {
            //Check Add Product
            $enableProductArray = [];
            if (!empty($oldProductArray)) {
                foreach ($oldProductArray as $productId) {
                    if (!in_array($productId, $newValueArray)) {
                        $enableProductArray[] = $productId;
                    }
                }
            }
            $this->processProduct($enableProductArray, $newValueArray, $storeId);
        }
        return parent::beforeSave();
    }

    /**
     * @param array $enableProductArray
     * @param array $newValueArray
     * @param string $storeId
     * @return $this
     * @throws \Exception
     */
    public function processProduct($enableProductArray, $newValueArray, $storeId)
    {
        //Enable Product
        foreach ($enableProductArray as $productId) {
            $productObject = $this->productFactory->create()->setStoreId($storeId)->load($productId);
            if (!$productObject->getId()) {
                throw new \Exception(__('No such entity with id = ') . $productId);
            }
            $productObject->setData('excluded_xml_sitemap', '0');
            $this->productResource->saveAttribute($productObject, 'excluded_xml_sitemap');
        }

        foreach ($newValueArray as $productId) {
            $productObject = $this->productFactory->create()->setStoreId($storeId)->load($productId);
            if (!$productObject->getId()) {
                throw new \Exception(__('No such entity with id = ') . $productId);
            }
            $productObject->setData('excluded_xml_sitemap', '1');
            $this->productResource->saveAttribute($productObject, 'excluded_xml_sitemap');
        }
        return $this;
    }
}
