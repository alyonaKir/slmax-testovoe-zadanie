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
namespace Bss\Redirects301Seo\Model\Config\Source;

/**
 * Class Redirects
 *
 * @package Bss\Redirects301Seo\Model\Config\Source
 */
class Redirects extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    public $pageFactory;

    /**
     * Redirects constructor.
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageFactory
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $this->getStoreId();

        $collection = $this->pageFactory->create();
        $collection->addFieldToFilter('is_active', \Magento\Cms\Model\Page::STATUS_ENABLED);
        $cms = [];
        foreach ($collection as $item) {
            $cms['cms_' . $item->getId()] = $item->getTitle();
        }
        $cmsArray = [];
        $count = 0;
        $cmsArray[$count]['value'] = 'category';
        $cmsArray[$count]['label'] = 'Parent Category';
        $count++;
        $cmsArray[$count]['value'] = 'category_priority';
        $cmsArray[$count]['label'] = 'Parent Category with Priority';
        $count++;
        $cmsArray[$count]['value'] = 'index';
        $cmsArray[$count]['label'] = 'Home Page';
        $count++;
        foreach ($cms as $id => $title) {
            if ($id != 'home') {
                $cmsArray[$count]['value'] = $id;
                $cmsArray[$count]['label'] = 'CMS Page: ' . $title;
                $count++;
            }
        }
        return $cmsArray;
    }
}
