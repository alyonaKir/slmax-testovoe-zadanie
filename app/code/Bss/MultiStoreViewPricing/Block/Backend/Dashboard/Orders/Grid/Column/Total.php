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
namespace Bss\MultiStoreViewPricing\Block\Backend\Dashboard\Orders\Grid\Column;

class Total extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var int
     */
    protected $_defaultWidth = 100;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Directory\Model\Currency\DefaultLocator
     */
    protected $currencyLocator;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator,
        array $data = []
    ) {
        $this->currencyFactory = $currencyFactory;
        $this->localeCurrency = $localeCurrency;
        $this->currencyLocator = $currencyLocator;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Zend_Currency_Exception
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($data = (string)$this->_getValue($row)) {
            $currencyCode = $this->_getCurrencyCode($row);
            $sign = (bool)(int)$this->getColumn()->getShowNumberSign() && $data > 0 ? '+' : '';
            $data = sprintf("%f", $data);
            $data = $this->localeCurrency->getCurrency($currencyCode)->toCurrency($data);
            return $sign . $data;
        }
        return $this->getColumn()->getDefault();
    }

    /**
     * Returns currency code, false on error
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function _getCurrencyCode($row)
    {
        if ($code = $this->getColumn()->getCurrencyCode()) {
            return $code;
        }

        if ($code = $row->getData($this->getColumn()->getCurrency())) {
            return $code;
        }

        if ($code = $row->getData('store_currency_code')) {
            return $code;
        }

        return $this->currencyLocator->getDefaultCurrency($this->_request);
    }
}
