<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-optimize
 * @version   2.0.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\Optimize\Model\Config\Source;


class YesNoFlat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Yes')], ['value' => 0, 'label' => __('No (Recommended)')]];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [0 => __('No (Recommended)'), 1 => __('Yes')];
    }
}
