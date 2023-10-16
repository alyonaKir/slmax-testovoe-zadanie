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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Model\Config\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class ExcludeSeoAltText
 * @package Bss\SeoAltText\Model\Config\Product
 */
class ExcludeSeoAltText extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [];
        $this->_options[] = ['label' => 'No', 'value' => '0'];
        $this->_options[] = ['label' => 'Yes', 'value' => '1'];
        return $this->_options;
    }
}
