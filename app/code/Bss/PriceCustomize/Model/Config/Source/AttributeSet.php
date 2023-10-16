<?php
namespace Bss\PriceCustomize\Model\Config\Source;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class AttributeSet implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Set collection factory
     *
     * @var CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @param EavConfig $eavConfig
     * @param CollectionFactory $attributeSetCollectionFactory
     */
    public function __construct(
        EavConfig $eavConfig,
        CollectionFactory $attributeSetCollectionFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [['value' => 0, 'label' => __("---Disabled---")]];
        $entityTypeId = $this->eavConfig
            ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getEntityTypeId();
        $collection = $this->attributeSetCollectionFactory->create();
        $collection->setEntityTypeFilter($entityTypeId)
            ->addFieldToSelect('attribute_set_id', 'value')
            ->addFieldToSelect('attribute_set_name', 'label')
            ->setOrder(
                'attribute_set_name',
                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
            );

        return array_merge($result, $collection->getData());
    }
}
