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
 * Class CalculationFee
 * @package Mageplaza\ShippingRules\Model\Config\Source
 */
class CalculationFee implements ArrayInterface
{
    const RE_CALCULATION     = 1;

    const ADD_EXTRA_FEE      = 2;

    const SUBTRACT_EXTRA_FEE = 3;

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
            self::RE_CALCULATION     => __('Re-calculate shipping fee'),
            self::ADD_EXTRA_FEE      => __('Add extra fee'),
            self::SUBTRACT_EXTRA_FEE => __('Subtract extra fee')
        ];
    }
}
