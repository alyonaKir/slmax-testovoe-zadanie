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
namespace Bss\ProductLabel\Plugin;

use Bss\ProductLabel\Block\Label;

/**
 * Class SetLabelDataInWishlistSidebar
 * @package Bss\ProductLabel\Plugin
 */
class SetLabelDataInWishlistSidebar
{
    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    private $wishlistHelper;

    /**
     * @var Label
     */
    private $label;

    /**
     * @var \Bss\ProductLabel\Helper\Data
     */
    private $helper;

    /**
     * SetLabelDataInWishlistSidebar constructor.
     * @param Label $label
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Bss\ProductLabel\Helper\Data $helper
     */
    public function __construct(
        Label $label,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Bss\ProductLabel\Helper\Data $helper
    ) {
        $this->label = $label;
        $this->wishlistHelper = $wishlistHelper;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Wishlist\CustomerData\Wishlist $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(
        \Magento\Wishlist\CustomerData\Wishlist $subject,
        $result
    ) {
        if (!$this->helper->isEnable()) {
            return $result;
        }

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setPageSize(count($result['items']))->setInStockFilter(true)->setOrder('added_at');
        foreach ($result['items'] as $key => $item) {
            foreach ($collection as $wishlistItem) {
                if ($item['product_url'] == $this->wishlistHelper->getProductUrl($wishlistItem)) {
                    $product = $wishlistItem->getProduct();
                    $result['items'][$key]['label_data'] =  $this->label->getLabelData($product);
                }
            }
        }
        return $result;
    }
}
