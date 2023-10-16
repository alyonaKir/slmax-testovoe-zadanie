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

namespace Mageplaza\PaymentRestriction\Ui\Component\Listing\Column\CustomerGroup;

use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package Mageplaza\PaymentRestriction\Ui\Component\Listing\Column\CustomerGroup
 */
class Options implements OptionSourceInterface
{
    /**
     * @var GroupFactory
     */
    protected $_customerGroupFactory;

    /**
     * Options constructor.
     *
     * @param GroupFactory $customerGroupFactory
     */
    public function __construct(GroupFactory $customerGroupFactory)
    {
        $this->_customerGroupFactory = $customerGroupFactory;
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
        /** @var Group $customerGroup */
        $customerGroup           = $this->_customerGroupFactory->create();
        $customerGroupCollection = $customerGroup->getCollection();

        if (count($customerGroupCollection)) {
            foreach ($customerGroupCollection as $item) {
                $options[] = [
                    'label' => $item->getCustomerGroupCode(),
                    'value' => $item->getCustomerGroupId(),
                ];
            }
        }

        return $options;
    }
}
