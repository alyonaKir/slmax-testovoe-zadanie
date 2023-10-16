<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ShoppingCartRulePerStoreView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ShoppingCartRulePerStoreView\Plugin\ResourceModel\Rule;

class Collection extends \Magento\SalesRule\Model\ResourceModel\Rule\Collection
{
    public $storeManager;
    public $state;
    public $request;
    public $session;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Magento\Backend\Model\Session\Quote $session
    ) {
        $this->request = $context->getRequest();
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->session = $session;
    }

    /**
     * Filter collection by store(s)
     * Filter collection to only active rules.
     * @return $this
     */
    public function afterAddWebsiteGroupDateFilter($subject, $result)
    {
        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $storeIds = [(int) $this->session->getStoreId()];
        } else {
            $storeIds = [(int) $this->storeManager->getStore()->getId()];
        }

        $entityInfo = $result->_getAssociatedEntityInfo('store');

        $result->getSelect()->join(
            ['store' => $result->getTable($entityInfo['associations_table'])],
            $result->getConnection()->quoteInto('store.' . $entityInfo['entity_id_field'] . ' IN (?)', $storeIds)
            . ' AND main_table.' . $entityInfo['rule_id_field'] . ' = store.' . $entityInfo['rule_id_field'],
            []
        );
        return $result;
    }
}
