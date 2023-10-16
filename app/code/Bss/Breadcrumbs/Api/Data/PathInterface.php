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
 * @package    Bss_Breadcrumbs
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Breadcrumbs\Api\Data;

interface PathInterface
{
    const ENTITY_ID = 'entity_id';
    const ATTRIBUTE_SET_ID= 'attribute_set_id';
    const PARENT_ID= 'parent_id';
    const CREATE_AT= 'created_at';
    const UPDATE_AT= 'updated_at';
    const PATH= 'path';
    const POSITION = 'position';
    const LEVEL = 'level';
    const CHILDREN_COUNT = 'children_count';
    const PRIORITY_ID = 'priority_id';

    /**
     * Get priority
     *
     * @return string|null
     */

    public function getPriority();

    /**
     * Set priority
     *
     * @param string $priority
     * @return mixed
     */
    public function setPriority($priority);
}
