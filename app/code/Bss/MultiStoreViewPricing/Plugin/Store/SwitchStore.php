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
namespace Bss\MultiStoreViewPricing\Plugin\Store;

class SwitchStore
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    public $cart;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    public $helper;

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        $this->cart = $cart;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if ($this->helper->isScopePrice()) {
            $store = $request->getParam('___store', false);
            if ($store) {
                $this->cart->updateItems([])->save();
            }
        }
        return $proceed($request);
    }
}
