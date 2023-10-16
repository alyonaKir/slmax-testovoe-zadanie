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
namespace Bss\MetaTagManager\Model\Config;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Category
 *
 * @package Bss\MetaTagManager\Model\Config
 */
class Category extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollection;

    /**
     * @var \Bss\MetaTagManager\Helper\Data
     */
    protected $helper;

    /**
     * Category constructor.
     * @param StoreManagerInterface $storeManager
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param \Bss\MetaTagManager\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryCollectionFactory $categoryCollectionFactory,
        \Bss\MetaTagManager\Helper\Data $helper,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollectionFactory;
        $this->helper = $helper;
        parent::__construct($data);
    }

    /**
     * Convert array to option
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $result = [];
        $cateCollection = $this->categoryCollection->create();
        $cateCollection->addAttributeToSelect('*');
        foreach ($cateCollection as $cate) {
            /* @var \Magento\Catalog\Model\Category $cate */
            if ($cate->getId() != 1) {
                $level = $cate->getLevel();
                $option = [
                    'value' =>  $cate->getId(),
                    'label' =>  $this->helper->addHypens($cate->getName(), $level)
                ];
                $result[] = $option;
            }
        }
        return $result;
    }
}
