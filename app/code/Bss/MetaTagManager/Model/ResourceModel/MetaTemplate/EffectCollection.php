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
namespace Bss\MetaTagManager\Model\ResourceModel\MetaTemplate;

use Bss\MetaTagManager\Model\MetaTemplateFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollection;

class EffectCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var MetaTemplateFactory
     */
    protected $metaTemplateFactory;

    /**
     * @var AttributeCollection
     */
    protected $attributeCollection;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * EffectCollection constructor.
     * @param ProductFactory $productFactory
     * @param MetaTemplateFactory $metaTemplateFactory
     * @param AttributeCollection $attributeCollection
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        ProductFactory $productFactory,
        MetaTemplateFactory $metaTemplateFactory,
        AttributeCollection $attributeCollection,
        CategoryRepository $categoryRepository
    ) {
        $this->productFactory = $productFactory;
        $this->metaTemplateFactory = $metaTemplateFactory;
        $this->attributeCollection = $attributeCollection;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param $categoryIds
     * @param $storeId
     * @return array
     */
    public function getTemplateEffect($categoryIds, $storeId)
    {
        $allParents = [];
        $resultTemplate = [];
        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                $thisParents = array_diff($category->getParentIds(), ['1']);
                $allParents = array_unique(array_merge($allParents, $thisParents));
            } catch (\Exception $e) {
                $this->_logger->critical($e->getMessage());
                continue;
            }
        }

        $allParents = array_diff($allParents, $categoryIds);
        $parentTemplateIds = $this->metaTemplateFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('use_sub', 1)
            ->addFieldToFilter('category', ['in' => $allParents])->getAllIds();
        $templateCollection = $this->metaTemplateFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('category', ['in' => $categoryIds])->getAllIds();
        $mergeIds = array_merge($parentTemplateIds, $templateCollection);

        $resultCollection = $this->metaTemplateFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('id', ['in' => $mergeIds]);
        $resultCollection->setOrder('priority', 'DESC');

        if ($resultCollection) {
            foreach ($resultCollection as $template) {
                $allowStore = $template->getStoreArray();
                if (in_array($storeId, $allowStore)) {
                    $resultTemplate = $template;
                }
            }
        }
        return $resultTemplate;
    }

    /**
     * @return $this
     */
    public function getProductAttributeVariables()
    {
        $collection = $this->attributeCollection->create()->addVisibleFilter();

        $collection->addFieldToFilter(
            [
                'is_searchable', 'attribute_code',
            ],
            [
                '1',
                [
                    ['in', ['special_price', 'country_of_manufacture']],
                ]
            ]
        )
            ->addFieldToFilter('frontend_input', [
                'text', 'textarea', 'select', 'price', 'swatch_visual', 'swatch_text', 'multiselect'
            ])
            ->addFieldToFilter('attribute_code', ['nin'=>['tax_class_id', 'status']]);

        return $collection;
    }
}
