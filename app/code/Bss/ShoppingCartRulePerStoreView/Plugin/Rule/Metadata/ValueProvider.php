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
namespace Bss\ShoppingCartRulePerStoreView\Plugin\Rule\Metadata;

use Magento\Store\Model\System\Store;

class ValueProvider
{
    public $store;

    public function __construct(
        Store $store
    ) {
        $this->store = $store;
    }

    /**
     * @param string $subject
     * @param array $result
     */
    public function afterGetMetadataValues($subject, $result)
    {
        $store_options = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'options' => $this->store->getStoreValuesForForm(),
                    ],
                ],
            ],
        ];
        $result['rule_information']['children']['website_ids'] = $store_options;
        return $result;
    }
}
