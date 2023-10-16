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

/**
 * Class ProductHelper
 * @package Bss\SeoAltText\Helper
 */
class ProductHelper
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $type;
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    private $status;
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $visibility;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    private $setsFactory;

    /**
     * ProductHelper constructor.
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->type = $type;
        $this->status = $status;
        $this->productFactory = $productFactory;
        $this->setsFactory = $setsFactory;
        $this->visibility = $visibility;
    }

    /**
     * @return array
     */
    public function getAttributeSet()
    {
        $sets = $this->setsFactory->create()->setEntityTypeFilter(
            $this->productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();
        return $sets;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Visibility
     */
    public function getVisibility()
    {
        return $this->visibility;
    }
}
