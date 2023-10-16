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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Model\Config\Source;

/**
 * Class EndDate
 * @package Bss\SeoReport\Model\Config\Source
 */
class EndDate extends \Magento\Framework\View\Element\Template
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $dataReturn = [];
        $dataReturn[] = [
            'value' => 'today',
            'label' => 'Current Date'
        ];
        $dataReturn[] = [
            'value' => 'custom',
            'label' => 'Custom Date'
        ];
        return $dataReturn;
    }
}
