<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_ShippingRestriction
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRestriction\Test\Unit\Model\Source\System;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mageplaza\ShippingRestriction\Model\Config\Source\Day;
use PHPUnit\Framework\TestCase;

/**
 * Class DayTest
 * @package Mageplaza\ShippingRestriction\Test\Unit\Model\Source\System
 */
class DayTest extends TestCase
{
    /**
     * @var Day
     */
    protected $model;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->model = $helper->getObject(
            Day::class
        );
    }

    /**
     * Test to actions option array
     */
    public function testToOptionArray()
    {
        $expectResult = [
            [
                'value' => 'monday',
                'label' => __('Monday')
            ],
            [
                'value' => 'tuesday',
                'label' => __('Tuesday')
            ],
            [
                'value' => 'wednesday',
                'label' => __('Wednesday')
            ],
            [
                'value' => 'thursday',
                'label' => __('Thursday')
            ],
            [
                'value' => 'friday',
                'label' => __('Friday')
            ],
            [
                'value' => 'saturday',
                'label' => __('Saturday')
            ],
            [
                'value' => 'sunday',
                'label' => __('Sunday')
            ],
        ];
        $actualResult = $this->model->toOptionArray();
        $this->assertEquals($expectResult, $actualResult);
    }
}
