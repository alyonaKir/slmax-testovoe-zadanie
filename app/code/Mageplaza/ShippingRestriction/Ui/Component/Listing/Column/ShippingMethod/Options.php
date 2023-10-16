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

namespace Mageplaza\ShippingRestriction\Ui\Component\Listing\Column\ShippingMethod;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\ShippingRestriction\Helper\Data as HelperData;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule;

/**
 * Class Options
 * @package Mageplaza\ShippingRestriction\Ui\Component\Listing\Column\ShippingMethod
 */
class Options implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Rule
     */
    protected $_shippingResource;

    /**
     * Options constructor.
     *
     * @param Escaper $escaper
     * @param HelperData $helperData
     * @param Rule $shippingResource
     */
    public function __construct(
        Escaper $escaper,
        HelperData $helperData,
        Rule $shippingResource
    ) {
        $this->escaper           = $escaper;
        $this->_helperData       = $helperData;
        $this->_shippingResource = $shippingResource;
    }

    /**
     * Get options
     *
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        return array_values($this->generateCurrentOptions());
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function generateCurrentOptions()
    {
        $options              = [];
        $pickedShipMethods    = $this->_shippingResource->getShippingMethods();
        $pickedShipMethods    = implode(',', $pickedShipMethods);
        $pickedShipMethodsArr = explode(',', $pickedShipMethods);
        $pickedShipMethodsArr = array_unique($pickedShipMethodsArr);

        $methodCollection = $this->_helperData->getShippingMethods();
        foreach ($methodCollection as $method) {
            $isExist = false;
            /** @var array[] $item */
            foreach ($method['value'] as $item) {
                if (in_array($item['value'], $pickedShipMethodsArr, true)) {
                    $isExist = true;
                    break;
                }
            }
            if ($isExist) {
                /** @var string $name */
                $name                    = $this->escaper->escapeHtml($method['label']);
                $options[$name]['label'] = $name;
                /** @var array[] $item */
                foreach ($method['value'] as $item) {
                    if (in_array($item['value'], $pickedShipMethodsArr, true)) {
                        $item['label']             = str_repeat(' ', 4) . $this->escaper->escapeHtml($item['label']);
                        $options[$name]['value'][] = $item;
                    }
                }
            }
        }

        return $options;
    }
}
