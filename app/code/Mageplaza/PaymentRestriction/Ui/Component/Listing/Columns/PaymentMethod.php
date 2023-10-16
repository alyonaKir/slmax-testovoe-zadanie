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
 * @package     Mageplaza_PaymentRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PaymentRestriction\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageplaza\PaymentRestriction\Helper\Data;

/**
 * Class PaymentMethod
 * @package Mageplaza\PaymentRestriction\Ui\Component\Listing\Columns
 */
class PaymentMethod extends Column
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var string
     */
    protected $paymentMethodKey;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * PaymentMethod constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param Data $helperData
     * @param array $components
     * @param array $data
     * @param string $paymentKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        Data $helperData,
        array $components = [],
        array $data = [],
        $paymentKey = 'payment_methods'
    ) {
        $this->escaper          = $escaper;
        $this->paymentMethodKey = $paymentKey;
        $this->_helperData      = $helperData;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = explode(',', $item[$this->getData('name')]);
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     *
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $html               = '';
        $origPaymentMethods = $item[$this->paymentMethodKey];

        if (!is_array($origPaymentMethods)) {
            $origPaymentMethods = [$origPaymentMethods];
        }
        $allMethods = $this->_helperData->getActiveMethods();

        foreach ($allMethods as $paymentMethodGroupTitle => $paymentMethodGroup) {
            $isExistMethod = false;
            if (is_array($paymentMethodGroup)) {
                foreach ($paymentMethodGroup as $paymentCode) {
                    if (in_array($paymentCode, $origPaymentMethods)) {
                        $isExistMethod = true;
                        break;
                    }
                }
                if ($isExistMethod) {
                    $html .= '<b>' . ucwords($paymentMethodGroupTitle) . '</b><br/>';
                    foreach ($paymentMethodGroup as $paymentCode) {
                        if (in_array($paymentCode, $origPaymentMethods)) {
                            $paymentTitle = $this->_helperData
                                ->getConfigValue('payment/' . $paymentCode . '/title');
                            $html         .= str_repeat(
                                '&nbsp;',
                                3
                            ) . $this->escaper->escapeHtml($paymentTitle) . '<br/>';
                        }
                    }
                }
            } elseif (in_array($paymentMethodGroup, $origPaymentMethods)) {
                $paymentTitle = $this->_helperData->getConfigValue('payment/' . $paymentMethodGroup . '/title');
                $html         .= $this->escaper->escapeHtml($paymentTitle) . '<br/>';
            }
        }

        return $html;
    }
}
