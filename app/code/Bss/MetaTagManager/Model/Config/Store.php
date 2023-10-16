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
 * Class Store
 *
 * @package Bss\MetaTagManager\Model\Config
 */
class Store extends \Magento\Framework\DataObject implements OptionSourceInterface
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
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Store constructor.
     * @param StoreManagerInterface $storeManager
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryCollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollectionFactory;
        $this->systemStore = $systemStore;
        parent::__construct($data);
    }

    /**
     * Convert array to options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $stores = $this->storeManager->getStores(false);
        foreach ($stores as $store) {
            $option = [
                'value' =>  $store->getId(),
                'label' =>  $store->getName()
            ];
            $result[] = $option;
        }
        return $result;
    }
}
