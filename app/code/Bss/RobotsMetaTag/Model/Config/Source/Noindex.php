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
 * @package    Bss_RobotsMetaTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RobotsMetaTag\Model\Config\Source;

/**
 * Class Noindex
 *
 * @package Bss\RobotsMetaTag\Model\Config\Source
 */
class Noindex implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    public $pageFactory;

    /**
     * @var \Magento\Cms\Model\Page
     */
    public $page;

    /**
     * Noindex constructor.
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->page = $page;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $page = $this->pageFactory->create();
        foreach ($page->getCollection() as $item) {
            $cms[$item->getIdentifier()] = $item->getTitle();
        }

        $cmsArray = [];
        $count = 0;
        $cmsArray[$count]['value'] = 'checkout';
        $cmsArray[$count]['label'] = 'Checkout Page';
        $count++;
        $cmsArray[$count]['value'] = 'contact';
        $cmsArray[$count]['label'] = 'Contact us Page';
        $count++;
        $cmsArray[$count]['value'] = 'customer_account';
        $cmsArray[$count]['label'] = 'Customer Account Pages';
        $count++;
        $cmsArray[$count]['value'] = 'product_compare';
        $cmsArray[$count]['label'] = 'Product Compare Pages';
        $count++;
        $cmsArray[$count]['value'] = 'rss_index_index';
        $cmsArray[$count]['label'] = 'RSS Feeds';
        $count++;
        $cmsArray[$count]['value'] = 'catalogsearch_result';
        $cmsArray[$count]['label'] = 'Search Result Pages';
        $count++;
        $cmsArray[$count]['value'] = 'wishlist';
        $cmsArray[$count]['label'] = 'Wishlist Pages';
        $count++;
        foreach ($cms as $id => $title) {
            $cmsArray[$count]['value'] = 'cms_' . $id;
            $cmsArray[$count]['label'] = 'CMS Page: ' . $title;
            $count++;
        }
        return $cmsArray;
    }
}
