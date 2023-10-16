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
 *  @category  BSS
 *  @package   Bss_ProductLabel
 *  @author    Extension Team
 *  @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Plugin;

use Magento\Quote\Model\Quote\Item;
use Bss\ProductLabel\Block\Label;

/**
 * Class SetLabelDataInMinicart
 * @package Bss\ProductLabel\Plugin
 */
class SetLabelDataInMinicart
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @var \Bss\ProductLabel\Helper\Data
     */
    private $helper;

    /**
     * SetLabelDataInMinicart constructor.
     * @param Label $label
     * @param \Bss\ProductLabel\Helper\Data $helper
     */
    public function __construct(Label $label, \Bss\ProductLabel\Helper\Data $helper)
    {
        $this->label = $label;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\CustomerData\AbstractItem $subject
     * @param \Closure $proceed
     * @param Item $item
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItemData(
        \Magento\Checkout\CustomerData\AbstractItem $subject,
        \Closure $proceed,
        Item $item
    ) {
        $data = $proceed($item);
        if (!$this->helper->isEnable()) {
            return $data;
        }

        $product = $item->getProduct();

        $label_data = ['label_data' => $this->label->getLabelData($product)];

        return array_merge($data, $label_data);
    }
}
