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
namespace Bss\ShoppingCartRulePerStoreView\Plugin\ResourceModel;

use Magento\SalesRule\Model\ResourceModel\Rule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;

class SaveHandler
{
    /**
     * @var Rule
     */
    public $ruleResource;

    /**
     * @param Rule $ruleResource
     */
    public function __construct(
        Rule $ruleResource
    ) {
        $this->ruleResource = $ruleResource;
    }

    /**
     * @param string $subject
     * @param array $result
     */
    public function afterExecute($subject, $result)
    {
        $linkField = 'rule_id';
        if (isset($result['store_ids'])) {
            $storeIds = $result['store_ids'];
            if (!is_array($storeIds)) {
                $storeIds = explode(',', (string)$storeIds);
            }
            $this->ruleResource->bindRuleToEntity($result[$linkField], $storeIds, 'store');
        }
        return $result;
    }
}
