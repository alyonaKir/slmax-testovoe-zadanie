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
namespace Bss\ProductLabel\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Label
 * Rendering the label
 * @package Bss\ProductLabel\Block
 */
class Label extends Template
{
    /**
     * @var \Bss\ProductLabel\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var Product
     *
     */
    public $product;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Label constructor.
     * @param Template\Context $context
     * @param \Bss\ProductLabel\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function __construct(
        Template\Context $context,
        \Bss\ProductLabel\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
        $this->layout = $context->getLayout();
        $this->_request = $request;
        $this->storeManager = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getFullActionName()
    {
        return $this->_request->getFullActionName();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLabelData($product = null)
    {
        $page = $this->getFullActionName();
        $data = [];
        if (!$this->helper->isSystemConfigAllow($page)) {
            return $data;
        }
        $product = $product ? $product : $this->getProduct();
        if ($product != null) {
            $data = $this->helper->getLabelData($product);

            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                // Only show child product's label for these pages:
                if ($this->getFullActionName() == 'catalog_product_view' ||
                    $this->getFullActionName() == 'catalog_category_view' ||
                    $this->getFullActionName() == 'catalogsearch_result_index') {
                    $data = array_merge($data, $this->handleConfigurableProduct($product));
                }
            }
        }
        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleConfigurableProduct($product)
    {
        $data = [];
        $storeId = $this->storeManager->getStore()->getId();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $usedProducts = $productTypeInstance->getUsedProducts($product);
        foreach ($usedProducts as $child) {
            $labeData = $this->helper->getLabelData($child);
            if (!empty($labeData)) {
                $data = array_merge($data, $labeData);
            }
        }
        return $data;
    }

    /**
     * Get Image Container Selector In Category Page

     * @return string
     */
    public function getSelectorProductList()
    {
        return $this->helper->getSelectorProductList();
    }

    /**
     * Get Image Container Selector In Product Page
     * @return string
     */
    public function getSelectorProduct()
    {
        return $this->helper->getSelectorProductPage();
    }
}
