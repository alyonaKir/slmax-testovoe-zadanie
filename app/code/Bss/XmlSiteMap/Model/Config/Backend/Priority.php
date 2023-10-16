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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Model\Config\Backend;

/**
 * Class Priority
 *
 * @package Bss\XmlSiteMap\Model\Config\Backend
 */
class Priority extends \Magento\Framework\App\Config\Value
{
    /**
     * Plugin
     *
     * @return $this|\Magento\Framework\App\Config\Value
     * @throws \Exception
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value < 0 || $value > 1) {
            throw new \Exception(__('The priority must be between 0 and 1.'));
        } elseif ($value == 0 && !($value === '0' || $value === '0.0')) {
            throw new \Exception(__('The priority must be between 0 and 1.'));
        }
        return $this;
    }
}
