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
namespace Bss\HrefLang\Model\Config\Source;

/**
 * Class StoreView
 *
 * @package Bss\HrefLang\Model\Config\Source
 */
class StoreView implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var $pageFactory
     */
    public $pageFactory;

    /**
     * @var $page
     *
     */
    public $page;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * StoreView constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Get store name
     *
     * @return \Magento\Store\Api\Data\GroupInterface[]
     */
    public function getStoreName()
    {
        return $this->storeManager->getGroups();
    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function toOptionArray()
    {
        $cms = [];
        foreach ($this->getStoreName() as $group) {
            $cms[$group->getId()] = $group->getName();
        }
        $cmsArray = [];
        $count = 0;
        foreach ($cms as $id => $title) {
            $cmsArray[$count]['value'] = $id;
            $cmsArray[$count]['label'] = $title;
            $count++;
        }
        return $cmsArray;
    }
}
