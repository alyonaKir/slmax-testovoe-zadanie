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

namespace Mageplaza\ShippingRestriction\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\SalesRule\Model\Coupon\CodegeneratorFactory;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection;
use Magento\SalesRule\Model\Rule as AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule as RuleResourceModel;

/**
 * Class Rule
 * @package Mageplaza\ShippingRestriction\Model
 * @method Rule setAction(int $action)
 * @method Rule setLocation(int $location)
 * @method Rule setSaleRulesActive(int $active)
 * @method Rule setSaleRulesInactive(int $inactive)
 * @method string getSchedule()
 * @method string getSaleRulesInactive()
 * @method string getSaleRulesActive()
 * @method string getShippingMethods()
 * @method string getLocation()
 * @method string getCreatedAt()
 * @method int getAction()
 */
class Rule extends AbstractModel
{
    /**
     * @var Rule\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CouponFactory $couponFactory
     * @param CodegeneratorFactory $codegenFactory
     * @param AbstractModel\Condition\CombineFactory $condCombineFactory
     * @param AbstractModel\Condition\Product\CombineFactory $condProdCombineF
     * @param Collection $couponCollection
     * @param StoreManagerInterface $storeManager
     * @param Rule\Condition\CombineFactory $combineFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CouponFactory $couponFactory,
        CodegeneratorFactory $codegenFactory,
        AbstractModel\Condition\CombineFactory $condCombineFactory,
        AbstractModel\Condition\Product\CombineFactory $condProdCombineF,
        Collection $couponCollection,
        StoreManagerInterface $storeManager,
        Rule\Condition\CombineFactory $combineFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $couponFactory,
            $codegenFactory,
            $condCombineFactory,
            $condProdCombineF,
            $couponCollection,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_shippingrestriction_rule';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_shippingrestriction_rule';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_shippingrestriction_rule';

    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(RuleResourceModel::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }
}
