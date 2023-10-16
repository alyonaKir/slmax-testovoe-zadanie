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
 * @package    Bss_RobotsMetaTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RobotsMetaTag\Block\System\Form\Field;

/**
 * Class Option
 *
 * @package Bss\RobotsMetaTag\Block\System\Form\Field
 */
class Option extends \Magento\Framework\View\Element\Html\Select
{

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $options = $this->getMyOption();
        if (!$this->getOptions()) {
            foreach ($options as $code => $option) {
                $this->addOption($code, $option);
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

    /**
     * Get option
     *
     * @return mixed
     */
    public function getMyOption()
    {
        $option['index, nofollow'] = 'INDEX, NOFOLLOW';
        $option['noindex, follow'] = 'NOINDEX, FOLLOW';
        $option['noindex, nofollow'] = 'NOINDEX, NOFOLLOW';
        return $option;
    }
}
