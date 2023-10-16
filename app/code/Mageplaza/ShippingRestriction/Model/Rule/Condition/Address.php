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
 * @package     Mageplaza_ShippingRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRestriction\Model\Rule\Condition;

/**
 * Address rule condition data model.
 */
class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if ($this->_defaultOperatorInputByType === null) {
            $this->_defaultOperatorInputByType           = parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['string'] =
                ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()', '*=', '=*'];
        }

        return $this->_defaultOperatorInputByType;
    }

    /**
     * Default operator options getter.
     *
     * Provides all possible operator options.
     *
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        $this->_defaultOperatorOptions = array_merge(parent::getDefaultOperatorOptions(), [
            '=*' => __('start from'),
            '*=' => __('end with'),
        ]);

        return $this->_defaultOperatorOptions;
    }

    /**
     * Validate product attribute value for condition
     *
     * @param object|array|int|string|float|bool $validatedValue product attribute value
     *
     * @return  bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();

        /**
         * Comparison operator
         */
        $option = $this->getOperatorForValidate();
        switch ($option) {
            case '*=':
                $result = preg_match('/' . $value . '$/', $validatedValue) ? true : false;
                break;
            case '=*':
                $result = preg_match('/^' . $value . '/', $validatedValue) ? true : false;
                break;
            default:
                $result = parent::validateAttribute($validatedValue);
        }

        return $result;
    }
}
