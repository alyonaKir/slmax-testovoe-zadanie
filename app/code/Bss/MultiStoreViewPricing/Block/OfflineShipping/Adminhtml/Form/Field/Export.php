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
namespace Bss\MultiStoreViewPricing\Block\OfflineShipping\Adminhtml\Form\Field;

class Export extends \Magento\OfflineShipping\Block\Adminhtml\Form\Field\Export
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );

        $params = ['store' => $buttonBlock->getRequest()->getParam('store')];

        $url = $this->_backendUrl->getUrl("*/*/exportTablerates", $params);
        $data = [
            'label' => __('Export CSV'),
            'onclick' => "setLocation('" .
                $url .
                "conditionName/' + $('carriers_tablerate_condition_name').value + '/tablerates.csv' )",
            'class' => '',
        ];

        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }
}
