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
 * @package    Bss_CanonicalTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CanonicalTag\Helper;

/**
 * Class ProductData
 *
 * @package Bss\CanonicalTag\Helper
 */
class ProductData extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Cms\Model\Page
     */
    public $cmsPage;

    /**
     * ProductData constructor.
     * @param \Magento\Cms\Model\Page $cmsPage
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Cms\Model\Page $cmsPage,
        \Magento\Framework\Registry $registry
    ) {
        $this->cmsPage = $cmsPage;
        $this->registry = $registry;
    }

    /**
     * Registry
     *
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Get current CMS page
     *
     * @return \Magento\Cms\Model\Page
     */
    public function getCurrentCms()
    {
        return $this->cmsPage;
    }
}
