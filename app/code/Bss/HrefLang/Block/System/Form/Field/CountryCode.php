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

use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\View\Element\Context;

/**
 * Class CountryCode
 *
 * @package Bss\HrefLang\Block\System\Form\Field
 */
class CountryCode extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var Country
     */
    protected $countryHelper;

    /**
     * CountryCode constructor.
     * @param Context $context
     * @param Country $countryHelper
     * @param array $data
     */
    public function __construct(Context $context, Country $countryHelper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->countryHelper = $countryHelper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('not_assign', 'Not Assign');
            foreach ($this->countryHelper->toOptionArray() as $value) {
                if ($value['value']) {
                    $this->addOption($value['value'], $value['label']);
                }
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
