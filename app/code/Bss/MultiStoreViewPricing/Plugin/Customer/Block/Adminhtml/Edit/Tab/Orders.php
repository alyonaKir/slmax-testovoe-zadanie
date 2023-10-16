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
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Plugin\Customer\Block\Adminhtml\Edit\Tab;

class Orders
{
    /**
     * @param \Magento\Customer\Block\Adminhtml\Edit\Tab\Orders $subject
     * @param string $columnId
     * @param array|\Magento\Framework\DataObject $column
     * @return array
     */
    public function beforeAddColumn($subject, $columnId, $column)
    {
        if ($columnId == 'grand_total') {
            unset($column['type']);
            $column['renderer'] = \Bss\MultiStoreViewPricing\Block\Adminhtml\Customer\Orders\Grid\Column\Currency::class;
        }

        return [$columnId, $column];
    }
}
