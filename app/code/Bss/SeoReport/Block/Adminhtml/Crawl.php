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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Block\Adminhtml;

use Bss\SeoReport\Helper\Data;

/**
 * Class Crawl
 * @package Bss\SeoReport\Block\Adminhtml
 */
class Crawl extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry = null;

    /**
     * @var \Bss\SeoReport\Model\ResourceModel\PostFactory
     */
    protected $reportLinksFactory;
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * Crawl constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Data $dataHelper
     * @param \Bss\SeoReport\Model\ReportLinksFactory $reportLinksFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Data $dataHelper,
        \Bss\SeoReport\Model\ReportLinksFactory $reportLinksFactory,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->reportLinksFactory = $reportLinksFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }
    /**
     * @return mixed
     */
    public function getTotalLink()
    {
        $reportLinksFactory = $this->reportLinksFactory->create()->getCollection();
        return $reportLinksFactory->getSize();
    }

    /**
     * @return string
     */
    public function getLinkAjax()
    {
        return $this->getUrl('seo_report/crawl/getlinks');
    }

    /**
     * @return string
     */
    public function getSeoReportLink()
    {
        return $this->getUrl('seo_report/dashboard/index');
    }

    /**
     * @return string
     */
    public function getLinkCrawl()
    {
        return $this->getUrl('seo_report/crawl/crawl');
    }
}
