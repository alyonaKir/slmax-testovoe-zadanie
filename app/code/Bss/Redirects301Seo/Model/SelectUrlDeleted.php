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
 * @package    Bss_Redirects301Seo
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Redirects301Seo\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class SelectUrlDeleted
 *
 * @package Bss\Redirects301Seo\Model
 */
class SelectUrlDeleted extends AbstractModel
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(\Bss\Redirects301Seo\Model\ResourceModel\SelectUrlDeleted::class);
    }
}
