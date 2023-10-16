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
namespace Bss\RobotsMetaTag\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Robots
 *
 * @package Bss\RobotsMetaTag\Observer
 */
class Robots implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $layoutFactory;

    /**
     * @var \Bss\RobotsMetaTag\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\RobotsMetaTag\Block\CanonicalTag
     */
    protected $robots;

    /**
     * Robots constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\View\Page\Config $layoutFactory
     * @param \Bss\RobotsMetaTag\Block\Robots $robots
     * @param \Bss\RobotsMetaTag\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Page\Config $layoutFactory,
        \Bss\RobotsMetaTag\Block\Robots $robots,
        \Bss\RobotsMetaTag\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->robots = $robots;
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $fullActionName = $this->request->getFullActionName();

        //For Robots Meta Tag
        $robotsCurrentUrl = $this->robots->getCurrentUrl();
        $robotsEnable = $this->helper->getEnableRobots($this->robots->getStoreId());
        $robotsUrls = $this->helper->getUrlRobots($this->robots->getStoreId());
        $robotsNoindex = $this->helper->getNoindexRobots($this->robots->getStoreId());

        $robotsNoindexArray = explode(',', $robotsNoindex);

        if ($robotsEnable == '1') {
            $this->addRobotsMeta($fullActionName, $robotsUrls, $robotsCurrentUrl, $robotsNoindexArray);
        }
    }

    /**
     * @param string $fullActionName
     * @param string $robotsUrls
     * @param string $robotsCurrentUrl
     * @param string $robotsNoindexArray
     * @SuppressWarnings(CyclomaticComplexity)
     */
    protected function addRobotsMeta($fullActionName, $robotsUrls, $robotsCurrentUrl, $robotsNoindexArray)
    {
        if ($fullActionName == 'catalog_product_view' && is_array($robotsUrls)) {
            $this->setupRobotsTagForProduct($robotsUrls, $robotsCurrentUrl);
        } else {
            $count = 0;
            if (is_array($robotsUrls)) {
                foreach ($robotsUrls as $robotsUrl) {
                    if ($this->helper->checkUrl($robotsCurrentUrl, $robotsUrl['url']) === true) {
                        $this->layoutFactory->setRobots($robotsUrl['option']);
                        $count = 1;
                    }
                }
            }
            $this->setupRobotsTagForCMS($robotsNoindexArray, $count);
        }
    }

    /**
     * Setup tag for product
     *
     * @param array $robotsUrls
     * @param string $robotsCurrentUrl
     */
    protected function setupRobotsTagForProduct($robotsUrls, $robotsCurrentUrl)
    {
        foreach ($robotsUrls as $robotsUrl) {
            if ($this->helper->checkPathUrl($robotsUrl['url']) === false &&
                $this->helper->checkUrl($robotsCurrentUrl, $robotsUrl['url']) === true) {
                $this->layoutFactory->setRobots($robotsUrl['option']);
            }
        }
    }

    /**
     * Setup for CMS
     *
     * @param array $robotsNoindexArray
     * @param int $count
     */
    protected function setupRobotsTagForCMS($robotsNoindexArray, $count)
    {
        foreach ($robotsNoindexArray as $robotsOption) {
            if (strpos($robotsOption, 'cms') === false) {
                if ($this->robots->checkTypePage($robotsOption) && $count == 0) {
                    $this->layoutFactory->setRobots('NOINDEX, FOLLOW');
                }
            } else {
                $cmsUrlKey = ltrim($robotsOption, 'cms_');
                if ($this->robots->checkTypePage('cms', $cmsUrlKey) && $count == 0) {
                    $this->layoutFactory->setRobots('NOINDEX, FOLLOW');
                }
            }
        }
    }
}
