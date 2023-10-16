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
 * @package    Bss_HrefLang
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HrefLang\Block\System\Form\Field;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CustomerGroup
 *
 * @package Bss\HrefLang\Block\System\Form\Field
 */
class CustomerGroup extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $storeManager;

    /**
     * CustomerGroup constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $stores = $this->storeManager->getStores(false);
            foreach ($stores as $store) {
                if (!(int)$store->getIsActive()) {
                    continue;
                }
                $this->addOption($store->getId(), $store->getName());
            }
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
