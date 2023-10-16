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
 * @package    Bss_CanonicalTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CanonicalTag\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Bss\CanonicalTag\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var SaveUrlFactory
     */
    private $saveUrlFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Bss\CanonicalTag\Model\SaveUrlFactory $saveUrlFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Bss\CanonicalTag\Model\SaveUrlFactory $saveUrlFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->productResource = $productResource;
        $this->productFactory = $productFactory;
        $this->saveUrlFactory = $saveUrlFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $this->addProductSeoCanonicalTag($eavSetup);
        //Start Update Data
        $setup->endSetup();

        if ($setup->getConnection()->isTableExists('bss_canonical_tag')) {
            $canonicalTagCollection = $this->saveUrlFactory->create()->getCollection();
            if ($canonicalTagCollection->getSize()) {
                foreach ($canonicalTagCollection as $dataCanonicalTag) {
                    $value = $dataCanonicalTag->getUrlValue();
                    if ($value !== '' && $value !== null && $value) {
                        //Save to Database
                        $productId = $dataCanonicalTag->getProductId();
                        $storeId = $dataCanonicalTag->getStore();
                        $productObject = $this->productFactory->create()->setStoreId($storeId)->load($productId);
                        $productObject->setData('seo_canonical_tag', $value);
                        $this->productResource->saveAttribute($productObject, 'seo_canonical_tag');
                    }
                }
            }

            $setup->startSetup();
            $connection = $setup->getConnection();
            $connection->dropTable($connection->getTableName('bss_canonical_tag'));
            $setup->endSetup();
        }
    }

    /**
     * @param $eavSetup
     */
    protected function addProductSeoCanonicalTag($eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'seo_canonical_tag',
            [
                'group' => 'Search Engine Optimization',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Use Another URL for Canonical Tag',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 600,
                'note' => 'Leave it blank if you want to use the default Canonical Tag'
            ]
        );
    }
}
