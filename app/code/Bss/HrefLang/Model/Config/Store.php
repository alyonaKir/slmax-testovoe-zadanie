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
namespace Bss\HrefLang\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Store
 *
 * @package Bss\HrefLang\Model\Config
 */
class Store extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Store constructor.
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $result = [];
        $stores = $this->storeManager->getStores(false);
        foreach ($stores as $store) {
            $option = [
                        'value' =>  $store->getId(),
                        'label' =>  $store->getName()
                    ];
            $result[]   =   $option;
        }
        return $result;
    }
}
