<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingRules
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRules\Model\Config\Source\OrderScope;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Mageplaza\ShippingRules\Model\Config\Source\OrderScope
 */
class Type implements ArrayInterface
{
    const DISABLE                             = 0;

    const PERCENTAGE_OF_CART_TOTAL            = 1;

    const FIXED_AMOUNT                        = 2;

    const PERCENTAGE_OF_ORIGINAL_SHIPPING_FEE = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::DISABLE                             => __('Disable'),
            self::PERCENTAGE_OF_CART_TOTAL            => __('Percentage of cart total'),
            self::FIXED_AMOUNT                        => __('Fixed Amount'),
            self::PERCENTAGE_OF_ORIGINAL_SHIPPING_FEE => __('Percentage of original shipping fee')
        ];
    }
}
