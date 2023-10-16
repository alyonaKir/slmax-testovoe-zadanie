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
namespace Bss\MultiStoreViewPricing\Model\Indexer\Product\TierPrice\System\Config;

class Option extends \Magento\Catalog\Model\Indexer\Product\Price\System\Config\PriceScope
{
    /**
     * Set after commit callback
     *
     * @return $this
     */
    public function afterDelete()
    {
        $this->_getResource()->addCommitCallback([$this, 'processValue']);
        return parent::afterDelete();
    }
}
