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

namespace Mageplaza\ShippingRestriction\Ui\Component\Listing\Column\CustomerGroup;

use Magento\Customer\Model\Group;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package Mageplaza\ShippingRestriction\Ui\Component\Listing\Column\CustomerGroup
 */
class Options implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_customerGrColFact;

    /**
     * Options constructor.
     *
     * @param CollectionFactory $customerGrColFact
     */
    public function __construct(CollectionFactory $customerGrColFact)
    {
        $this->_customerGrColFact = $customerGrColFact;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_generateCustomerGroupOptions();
    }

    /**
     * Get customer group options
     *
     * @return array
     */
    protected function _generateCustomerGroupOptions()
    {
        $options = [];
        /** @var Collection $customerGroup */
        $customerGroupCol = $this->_customerGrColFact->create();

        if ($customerGroupCol !== null) {
            foreach ($customerGroupCol as $group) {
                /** @var Group $group */
                $options[] = [
                    'label' => $group->getCustomerGroupCode(),
                    'value' => $group->getCustomerGroupId(),
                ];
            }
        }

        return $options;
    }
}
