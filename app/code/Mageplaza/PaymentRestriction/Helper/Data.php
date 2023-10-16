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

namespace Mageplaza\PaymentRestriction\Helper;

use DateTimeZone;
use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\PaymentRestriction\Model\Config\Source\Location;
use Mageplaza\PaymentRestriction\Model\ResourceModel\Rule\Collection;
use Mageplaza\PaymentRestriction\Model\Rule;
use Mageplaza\PaymentRestriction\Model\RuleFactory as PaymentRestrictionRuleFactory;

/**
 * Class Data
 * @package Mageplaza\PaymentRestriction\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'mppaymentrestriction';

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
     * @type ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var PaymentRestrictionRuleFactory
     */
    protected $_paymentRestrictionRuleFactory;

    /**
     * @var State
     */
    protected $state;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param State $state
     * @param Config $shippingConfig
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param CustomerSession $customerSession
     * @param PaymentRestrictionRuleFactory $paymentRestrictionRuleFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        State $state,
        Config $shippingConfig,
        DateTime $dateTime,
        TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        PaymentRestrictionRuleFactory $paymentRestrictionRuleFactory
    ) {
        $this->_shippingConfig                = $shippingConfig;
        $this->_dateTime                      = $dateTime;
        $this->state                          = $state;
        $this->_localeDate                    = $localeDate;
        $this->_customerSession               = $customerSession;
        $this->_scopeConfig                   = $context->getScopeConfig();
        $this->_paymentRestrictionRuleFactory = $paymentRestrictionRuleFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param Address $shippingAddress
     * @param array $appliedSaleRuleIds
     * @param null $customerGroupId
     * @param null $currentWebsiteId
     *
     * @param null $storeId
     *
     * @return bool|Rule|mixed|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkApplyRule(
        $shippingAddress,
        $appliedSaleRuleIds,
        $customerGroupId = null,
        $currentWebsiteId = null,
        $storeId = null
    ) {
        $ruleActive     = false;
        $appliedRule    = false;
        $ruleCollection = $this->getPaymentRestrictionRuleCollection($customerGroupId, $storeId);
        $location       = $this->state->getAreaCode() === Area::AREA_ADMINHTML
            ? Location::ORDER_BACKEND : Location::ORDER_FRONTEND;
        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            $ruleLocations = $rule->getLocation();
            $ruleLocations = explode(',', $ruleLocations);
            if (in_array($location, $ruleLocations, true) && $this->getScheduleFilter($rule, $currentWebsiteId)) {
                if ($rule->getSaleRulesInactive()) {
                    $saleRuleInactive = explode(',', $rule->getSaleRulesInactive());
                    foreach ($saleRuleInactive as $inActive) {
                        if (in_array($inActive, $appliedSaleRuleIds, true)) {
                            $ruleActive = true;
                            break;
                        }
                    }
                    if ($ruleActive) {
                        $appliedRule = null;
                        break;
                    }
                    break;
                }
                if ($rule->getSaleRulesActive()) {
                    $saleRuleActive = explode(',', $rule->getSaleRulesActive());
                    foreach ($saleRuleActive as $active) {
                        if (in_array($active, $appliedSaleRuleIds, true)) {
                            $ruleActive = true;
                            break;
                        }
                    }
                    if ($ruleActive) {
                        $appliedRule = $rule;
                        break;
                    }
                    break;
                }
                if ($rule->validate($shippingAddress)) {
                    $appliedRule = $rule;
                    break;
                }
            }
        }

        return $appliedRule;
    }

    /**
     * @param null $customerGroupId
     * @param null $storeId
     *
     * @return AbstractCollection
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getPaymentRestrictionRuleCollection($customerGroupId = null, $storeId = null)
    {
        /** @var Collection $collection */
        $collection = $this->_paymentRestrictionRuleFactory
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
     * @param Collection $collection
     * @param null $customerGroupId
     *
     * @return Collection
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
     * @param Collection $collection
     *
     * @return mixed
     * @throws Exception
     */
    public function addDateFilter($collection)
    {
        $currentDateTime = new \DateTime($this->_dateTime->date(), new DateTimeZone('UTC'));
        $currentDateTime->setTimezone(new DateTimeZone($this->_localeDate->getConfigTimezone()));
        $dateTime = $currentDateTime->format('Y-m-d H:i:s');

        $collection->addFieldToFilter(['started_at', 'started_at'], [['to' => $dateTime], ['null' => true]])
            ->addFieldToFilter(['finished_at', 'finished_at'], [['from' => $dateTime], ['null' => true]]);

        return $collection;
    }

    /**
     * @param Rule $rule
     * @param null $currentWebsiteId
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getScheduleFilter($rule, $currentWebsiteId = null)
    {
        $dateTime         = new \DateTime($this->_dateTime->date(), new DateTimeZone('UTC'));
        $currentWebsiteId = $currentWebsiteId ?: $this->storeManager->getStore()->getWebsiteId();
        $timeZone         = $this->getConfigValue(
            'general/locale/timezone',
            $currentWebsiteId,
            ScopeInterface::SCOPE_WEBSITE
        );
        $dateTime->setTimezone(new DateTimeZone($timeZone));
        $currentDayOfWeek = strtolower($dateTime->format('l'));
        $currentTime      = strtotime($dateTime->format('H:i'));
        $ruleSchedule     = self::jsonDecode($rule->getSchedule());
        if (in_array($currentDayOfWeek, $ruleSchedule['day'], false)) {
            $fromTime = $ruleSchedule['from_time'][0] . ':' . $ruleSchedule['from_time'][1];
            $toTime   = $ruleSchedule['to_time'][0] . ':' . $ruleSchedule['to_time'][1];
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

    /**
     * Get all active payment method
     *
     * @return array
     */
    public function getActiveMethods()
    {
        $methodGroups = [];
        $allMethods   = [];

        /** @var array $paymentConfig */
        $paymentConfig = $this->_scopeConfig->getValue('payment', ScopeInterface::SCOPE_STORE);
        foreach ($paymentConfig as $methodCode => $methodValue) {
            if (isset($methodValue['active'], $methodValue['model'], $methodValue['group'])
                && (bool) $methodValue['active']) {
                $methodGroups[$methodValue['group']] = $methodValue['group'];
            }
        }
        $methodGroups = array_unique($methodGroups);
        foreach ($paymentConfig as $methodCode => $methodValue) {
            if (isset($methodValue['active'], $methodValue['model']) && (bool) $methodValue['active']) {
                if (isset($methodValue['group']) && in_array($methodValue['group'], $methodGroups, false)) {
                    $allMethods [$methodValue['group']][] = $methodCode;
                } else {
                    $allMethods [] = $methodCode;
                }
            }
        }

        return $allMethods;
    }
}
