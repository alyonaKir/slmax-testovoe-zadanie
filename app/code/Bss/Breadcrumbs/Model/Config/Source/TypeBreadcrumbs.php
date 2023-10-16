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
 * @package    Bss_Breadcrumbs
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Breadcrumbs\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TypeBreadcrumbs
 *
 * @package Bss\Breadcrumbs\Model\Config\Source
 */
class TypeBreadcrumbs implements ArrayInterface
{
    /**
     * Return list of TrueFalse Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'short',
                'label' => __('Use Short URL Path')
            ],
            [
                'value' => 'long',
                'label' => __('Use Long URL Path')
            ]
        ];
    }
}
