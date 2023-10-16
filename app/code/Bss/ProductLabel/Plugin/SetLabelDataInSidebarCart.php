<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Plugin;

use Bss\ProductLabel\Block\Label;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class SetLabelDataInSidebarCart
 * @package Bss\ProductLabel\Plugin
 */
class SetLabelDataInSidebarCart
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var \Bss\ProductLabel\Helper\Data
     */
    private $helper;

    /**
     * SetLabelDataInSidebarCart constructor.
     * @param Label $label
     * @param CheckoutSession $checkoutSession
     * @param \Bss\ProductLabel\Helper\Data $helper
     */
    public function __construct(
        Label $label,
        CheckoutSession $checkoutSession,
        \Bss\ProductLabel\Helper\Data $helper
    ) {
        $this->label = $label;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        if (!$this->helper->isEnable()) {
            return $result;
        }

        $items = $result['totalsData']['items'];
        foreach ($items as $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $product = $quoteItem->getProduct();

            if ($product) {
                $result['imageData'][$item['item_id']]['label_data'] = $this->label->getLabelData($product);
            }
        }
        return $result;
    }
}
