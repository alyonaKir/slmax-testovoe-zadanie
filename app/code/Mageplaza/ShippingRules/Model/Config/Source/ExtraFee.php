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

namespace Mageplaza\ShippingRules\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ExtraFee
 * @package Mageplaza\ShippingRules\Model\Config\Source
 */
class ExtraFee implements ArrayInterface
{
    const TAX      = 1;

    const DISCOUNT = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['value' => '', 'label' => __('-- Please select --')];

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
            self::TAX      => __('Tax'),
            self::DISCOUNT => __('Discount')
        ];
    }
}
