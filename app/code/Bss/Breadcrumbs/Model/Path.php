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
namespace Bss\Breadcrumbs\Model;

use Bss\Breadcrumbs\Api\Data\PathInterface;

/**
 * Class Path
 *
 * @package Bss\Breadcrumbs\Model
 */
class Path extends \Magento\Framework\Model\AbstractModel implements PathInterface
{

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\Breadcrumbs\Model\ResourceModel\Path::class);
    }

    /**
     * Set priority
     *
     * @param string $priorityId
     * @return $this
     */
    public function setPriority($priorityId)
    {
        return $this->setData(self::PRIORITY_ID, $priorityId);
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY_ID);
    }
}
