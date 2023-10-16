<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Model\Cart;

use Bss\MultiStoreViewPricing\Model\Cart\StoreProvider as ConfigProvider;

class ConfigPlugin
{
    /**
     * @var ConfigProvider
     */
    public $configProvider;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    public $helper;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider,
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        $this->configProvider = $configProvider;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Sidebar $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(\Magento\Checkout\Block\Cart\Sidebar $subject, array $result)
    {
        if (!$this->helper->isScopePrice()) {
            return $result;
        }
        return array_merge_recursive($result, $this->configProvider->getConfig());
    }
}
