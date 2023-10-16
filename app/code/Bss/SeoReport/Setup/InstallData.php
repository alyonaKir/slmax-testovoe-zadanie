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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $this->addMainKeyword($eavSetup);

        $setup->getConnection()->addColumn(
            $setup->getTable('cms_page'),
            'main_keyword',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'comment' => 'Main Keyword'
            ]
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'meta_description',
            [
                'note' => 'Maximum 255 chars. Meta Description should optimally be between 200-255 characters'
            ]
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'meta_title',
            [
                'note' => 'Meta Title should optimally be between 50-70 characters'
            ]
        );

        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'main_keyword',
            [
                'type' => 'varchar',
                'label' => 'Main Keyword',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'sort_order' => 620,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
                'note' => ''
            ]
        );
        $idg = $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idg,
            'main_keyword',
            46
        );
        $setup->endSetup();
    }

    /**
     * @param object $eavSetup
     */
    protected function addMainKeyword($eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'main_keyword',
            [
                'group' => 'Search Engine Optimization',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Main Keyword',
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
                'sort_order' => 620,
                'apply_to' => ''
            ]
        );
    }
}
