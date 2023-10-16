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
 * @package     Mageplaza_ShippingRules
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRules\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\ShippingRules\Model\RuleFactory as ShippingRuleFactory;

/**
 * Class Data
 * @package Mageplaza\ShippingRules\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'mpshippingrules';

    /**
     * @var Config
     */
    protected $_shippingConfig;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var ShippingRuleFactory
     */
    protected $_shippingRuleFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Config $shippingConfig
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param CustomerSession $customerSession
     * @param ShippingRuleFactory $shippingRuleFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Config $shippingConfig,
        DateTime $dateTime,
        TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        ShippingRuleFactory $shippingRuleFactory
    ) {
        $this->_shippingConfig = $shippingConfig;
        $this->_dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->_customerSession = $customerSession;
        $this->_shippingRuleFactory = $shippingRuleFactory;

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
        $methods = [];
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = [];
            $carrierTitle = '';
            if (is_array($carrierModel->getAllowedMethods())) {
                foreach ($carrierModel->getAllowedMethods() as $methodCode => $method) {
                    $code = $carrierCode . '_' . $methodCode;
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
     * @throws \Exception
     */
    public function getShippingRuleCollection($customerGroupId = null, $storeId = null)
    {
        $collection = $this->_shippingRuleFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->setOrder('priority', 'asc');
        $this->addStoreFilter($collection, $storeId);
        $this->addCustomerGroupFilter($collection, $customerGroupId);
        $this->addDateFilter($collection);

        return $collection;
    }

    /**
     * Filter by store
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @param null $storeId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addStoreFilter($collection, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $collection->addFieldToFilter('main_table.store_ids', [
            ['finset' => Store::DEFAULT_STORE_ID],
            ['finset' => $storeId]
        ]);

        return $collection;
    }

    /**
     * @param $collection
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
     * Filter by Date
     *
     * @param $collection
     *
     * @return mixed
     * @throws \Exception
     */
    public function addDateFilter($collection)
    {
        $currentDateTime = new \DateTime($this->_dateTime->date(), new \DateTimeZone('UTC'));
        $currentDateTime->setTimezone(new \DateTimeZone($this->_localeDate->getConfigTimezone()));
        $dateTime = $currentDateTime->format('Y-m-d H:i:s');

        $collection->addFieldToFilter('started_at', ['to' => $dateTime])
            ->addFieldToFilter(['finished_at', 'finished_at'], [['from' => $dateTime], ['null' => true]]);

        return $collection;
    }

    /**
     * Check rule schedule
     *
     * @param $rule
     * @param null $currentWebsiteId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getScheduleFilter($rule, $currentWebsiteId = null)
    {
        $dateTime = (new \DateTime($this->_dateTime->date(), new \DateTimeZone('UTC')));
        $currentWebsiteId = ($currentWebsiteId) ?: $this->storeManager->getStore()->getWebsiteId();
        $dateTime->setTimezone(new \DateTimeZone($this->getConfigValue('general/locale/timezone', $currentWebsiteId, ScopeInterface::SCOPE_WEBSITE)));
        $currentDayOfWeek = strtolower($dateTime->format('l'));
        $currentTime = strtotime($dateTime->format('H:i'));
        $ruleSchedule = $this->jsonDecode($rule->getSchedule());
        if (in_array($currentDayOfWeek, $ruleSchedule['day'])) {
            $fromTime = $ruleSchedule['from_time'][0] . ':' . $ruleSchedule['from_time'][1];
            $toTime = $ruleSchedule['to_time'][0] . ':' . $ruleSchedule['to_time'][1];
            if ($currentTime >= strtotime($fromTime) && $currentTime <= strtotime($toTime)) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
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
}
