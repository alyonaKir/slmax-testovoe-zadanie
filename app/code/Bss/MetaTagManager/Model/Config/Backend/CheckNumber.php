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
namespace Bss\MetaTagManager\Model\Config\Backend;

/**
 * Class CheckNumber
 *
 * @package Bss\MetaTagManager\Model\Config\Backend
 */
class CheckNumber extends \Magento\Framework\App\Config\Value
{
    /**
     * Plugin before Save
     *
     * @return $this
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function beforeSave()
    {
        $label = $this->getData('field_config/label');

        $value = $this->getValue();
        if ($value !== '' && is_numeric($value) && $value < 0) {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' is less than 0.'));
        }
        if ($value !== '' && !is_numeric($value)) {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' is not a number.'));
        }
        return parent::beforeSave();
    }
}
