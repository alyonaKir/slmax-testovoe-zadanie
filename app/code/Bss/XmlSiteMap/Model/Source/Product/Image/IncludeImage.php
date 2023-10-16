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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Model\Source\Product\Image;

/**
 * Class IncludeImage
 *
 * @package Bss\XmlSiteMap\Model\Source\Product\Image
 */
class IncludeImage implements \Magento\Framework\Option\ArrayInterface
{
    const INCLUDE_NONE = 'none';

    const INCLUDE_BASE = 'base';

    const INCLUDE_ALL = 'all';

    /**
     * Retrieve options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::INCLUDE_NONE => __('None'),
            self::INCLUDE_BASE => __('Base Only'),
            self::INCLUDE_ALL => __('All')
        ];
    }
}
