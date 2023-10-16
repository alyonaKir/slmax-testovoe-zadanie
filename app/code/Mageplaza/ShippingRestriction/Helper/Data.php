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

namespace Mageplaza\ShippingRestriction\Helper;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as StdlibDateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule\Collection;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule\CollectionFactory as ShippingRuleColFact;
use Mageplaza\ShippingRestriction\Model\Rule;

/**
 * Class Data
 * @package Mageplaza\ShippingRestriction\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'mpshippingrestriction';

    /**
     * @var Config
     */
    protected $_shippingConfig;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var StdlibDateTime
     */
    protected $_dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var ShippingRuleColFact
     */
    protected $_shippingRuleColFact;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Config $shippingConfig
     * @param CustomerSession $customerSession
     * @param StdlibDateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param ShippingRuleColFact $shippingRuleColFact
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Config $shippingConfig,
        CustomerSession $customerSession,
        StdlibDateTime $dateTime,
        TimezoneInterface $localeDate,
        ShippingRuleColFact $shippingRuleColFact
    ) {
        $this->_shippingConfig      = $shippingConfig;
        $this->_customerSession     = $customerSession;
        $this->_dateTime            = $dateTime;
        $this->_localeDate          = $localeDate;
        $this->_shippingRuleColFact = $shippingRuleColFact;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Get all shipping methods
     *
     * @return array
     */
    public function getShippingMethods()
    {
        $activeCarriers = $this->_shippingConfig->getAllCarriers();
        $methods        = [];
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options      = [];
            $carrierTitle = '';

            $allowMethods = $carrierModel->getAllowedMethods();
            if (is_array($allowMethods)) {
                foreach ($allowMethods as $methodCode => $method) {
                    $code      = $carrierCode . '_' . $methodCode;
                    $options[] = ['value' => $code, 'label' => $method];
                }
                $carrierTitle = $this->getConfigValue('carriers/' . $carrierCode . '/title');
            }
            $methods[] = [
                'value' => $options,
                'label' => $carrierTitle
            ];
        }

        return $methods;
    }

    /**
     * @param null $customerGroupId
     * @param null $storeId
     *
     * @return mixed
     * @throws Exception
     */
    public function getShippingRuleCollection($customerGroupId = null, $storeId = null)
    {
        /** @var Collection $collection */
        $collection = $this->_shippingRuleColFact->create();
        $collection->addFieldToFilter('status', 1)->setOrder('priority', 'asc');
        $this->addStoreFilter($collection, $storeId);
        $this->addCustomerGroupFilter($collection, $customerGroupId);
        $this->addDateFilter($collection);

        return $collection;
    }

    /**
     * Filter by store
     *
     * @param AbstractCollection $collection
     * @param null $storeId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function addStoreFilter($collection, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $collection->addFieldToFilter('main_table.store_ids', [
            ['finset' => Store::DEFAULT_STORE_ID],
            ['finset' => $storeId]
        ]);

        return $collection;
    }

    /**
     * @param AbstractCollection $collection
     * @param null $customerGroupId
     *
     * @return mixed
     */
    public function addCustomerGroupFilter($collection, $customerGroupId = null)
    {
        $customerGroupId = $customerGroupId ?: $this->getCustomerGroupId();

        $collection->addFieldToFilter('main_table.customer_group', [
            ['finset' => $customerGroupId]
        ]);

        return $collection;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession->getCustomer()->getGroupId();
        }

        return 0;
    }

    /**
     * Filter by Date
     *
     * @param AbstractCollection $collection
     *
     * @return mixed
     * @throws Exception
     */
    public function addDateFilter($collection)
    {
        $currentDateTime = new DateTime($this->_dateTime->date(), new DateTimeZone('UTC'));
        $currentDateTime->setTimezone(new DateTimeZone($this->_localeDate->getConfigTimezone()));
        $dateTime = $currentDateTime->format('Y-m-d H:i:s');

        $collection->addFieldToFilter('started_at', ['to' => $dateTime])
            ->addFieldToFilter(['finished_at', 'finished_at'], [['from' => $dateTime], ['null' => true]]);

        return $collection;
    }

    /**
     * Check rule schedule
     *
     * @param Rule $rule
     * @param null $currentWebsiteId
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function isInScheduled($rule, $currentWebsiteId = null)
    {
        $dateTime         = new DateTime($this->_dateTime->date(), new DateTimeZone('UTC'));
        $currentWebsiteId = $currentWebsiteId ?: $this->storeManager->getStore()->getWebsiteId();
        $dateTime->setTimezone(new DateTimeZone(
            $this->getConfigValue(
                'general/locale/timezone',
                $currentWebsiteId,
                ScopeInterface::SCOPE_WEBSITE
            )
        ));

        $result = false;

        $currentDayOfWeek = strtolower($dateTime->format('l'));
        $currentTime      = strtotime($dateTime->format('H:i'));
        $ruleSchedule     = self::jsonDecode($rule->getSchedule());
        if (in_array($currentDayOfWeek, $ruleSchedule['day'], true)) {
            $fromTime = $ruleSchedule['from_time'][0] . ':' . $ruleSchedule['from_time'][1];
            $toTime   = $ruleSchedule['to_time'][0] . ':' . $ruleSchedule['to_time'][1];
            $result   = ($currentTime >= strtotime($fromTime) && $currentTime <= strtotime($toTime));
        }

        return $result;
    }
}
