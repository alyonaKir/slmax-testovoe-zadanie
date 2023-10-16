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
namespace Bss\ProductLabel\Helper;

/**
 * Class ModelLabel
 * @package Bss\ProductLabel\Helper
 */
class ModelLabel extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $condCombineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $condProdCombineF;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $action;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * ModelLabel constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data $helper
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $condProdCombineF
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $action
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Data $helper,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $condProdCombineF,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
    }

    /**
     * @return Data
     */
    public function getHelperData()
    {
        return $this->helper;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    public function getProductAction()
    {
        return $this->action;
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    public function getCondCombineFactory()
    {
        return $this->condCombineFactory;
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    public function getCondProdCombineF()
    {
        return $this->condProdCombineF;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getProductFactory()
    {
        return $this->productFactory;
    }
}
