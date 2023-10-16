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

namespace Mageplaza\ShippingRestriction\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageplaza\ShippingRestriction\Helper\Data;

/**
 * Class ShippingMethod
 * @package Mageplaza\ShippingRestriction\Ui\Component\Listing\Columns
 */
class ShippingMethod extends Column
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
    protected $shippingMethodKey;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * ShippingMethod constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param Data $helperData
     * @param array $components
     * @param array $data
     * @param string $storeKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        Data $helperData,
        array $components = [],
        array $data = [],
        $storeKey = 'shipping_methods'
    ) {
        $this->escaper           = $escaper;
        $this->shippingMethodKey = $storeKey;
        $this->_helperData       = $helperData;

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
            /** @var array[][] $item */
            foreach ($dataSource['data']['items'] as & $item) {
                $name                         = $this->getData('name');
                $item[$name]                  = explode(',', $item[$name]);
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
        $html                = '';
        $origShippingMethods = $item[$this->shippingMethodKey];

        if (!is_array($origShippingMethods)) {
            $origShippingMethods = [$origShippingMethods];
        }
        $carriers = $this->_helperData->getShippingMethods();

        foreach ($carriers as $carrier) {
            $isExistCarrier = false;
            /** @var array[] $child */
            foreach ($carrier['value'] as $child) {
                if (in_array($child['value'], $origShippingMethods, true)) {
                    $isExistCarrier = true;
                    break;
                }
            }
            if ($isExistCarrier) {
                $html .= '<b>' . $carrier['label'] . '</b><br/>';
                /** @var array[] $child */
                foreach ($carrier['value'] as $child) {
                    if (in_array($child['value'], $origShippingMethods, true)) {
                        $html .= str_repeat('&nbsp;', 3) . $this->escaper->escapeHtml($child['label']) . '<br/>';
                    }
                }
            }
        }

        return $html;
    }
}
