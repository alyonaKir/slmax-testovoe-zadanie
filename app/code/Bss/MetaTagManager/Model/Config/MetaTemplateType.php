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
namespace Bss\MetaTagManager\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CategoryGrid
 *
 * @package Bss\MetaTagManager\Model\Config
 */
class MetaTemplateType extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $option = [
            'value' =>  'product',
            'label' =>  'Product'
        ];
        $result[] = $option;
        $option = [
            'value' =>  'category',
            'label' =>  'Category'
        ];
        $result[] = $option;
        return $result;
    }
}
