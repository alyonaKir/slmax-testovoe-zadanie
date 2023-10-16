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
namespace Bss\ProductLabel\Block\Product;

/**
 * Class SetProductInCompare
 * @package Bss\ProductLabel\Model\Product
 */
class SetProductInCompare extends \Magento\Catalog\Block\Product\Compare\ListCompare
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * SetProductInCompare constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $itemCollectionFactory,
            $catalogProductVisibility,
            $customerVisitor,
            $httpContext,
            $currentCustomer,
            $data
        );
        $this->registry = $context->getRegistry();
    }

    /**
     * @param \Magento\Catalog\Model\Product $item
     */
    public function setProduct($item)
    {
        $this->registry->unregister('bss_init_product');
        $this->registry->unregister('bss_current_product');
        $this->registry->register('bss_current_product', $item);
    }
}
