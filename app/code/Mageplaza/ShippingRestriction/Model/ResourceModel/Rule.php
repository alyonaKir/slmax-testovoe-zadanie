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

namespace Mageplaza\ShippingRestriction\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Rule
 * @package Mageplaza\ShippingRestriction\Model\ResourceModel
 */
class Rule extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_shippingrestriction_rule', 'rule_id');
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }
        if (is_array($object->getCustomerGroup())) {
            $object->setCustomerGroup(implode(',', $object->getCustomerGroup()));
        }
        if (is_array($object->getSaleRulesActive())) {
            $object->setSaleRulesActive(implode(',', $object->getSaleRulesActive()));
        }
        if (is_array($object->getSaleRulesInactive())) {
            $object->setSaleRulesInactive(implode(',', $object->getSaleRulesInactive()));
        }
        if (is_array($object->getShippingMethods())) {
            $object->setShippingMethods(implode(',', $object->getShippingMethods()));
        }
        if (is_array($object->getLocation())) {
            $object->setLocation(implode(',', $object->getLocation()));
        }

        return $this;
    }

    /**
     * Get all shipping method which is assigned to Shipping Rule
     *
     * @return array
     * @throws LocalizedException
     */
    public function getShippingMethods()
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'shipping_methods');

        return $adapter->fetchCol($select);
    }
}
