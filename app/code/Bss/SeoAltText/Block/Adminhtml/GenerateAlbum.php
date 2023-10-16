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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Block\Adminhtml;

/**
 * Class GenerateAlbum
 * @package Bss\SeoAltText\Block\Adminhtml
 */
class GenerateAlbum extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Bss\SeoAltText\Helper\Data
     */
    public $dataHelper;

    /**
     * GenerateAlbum constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Bss\SeoAltText\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Bss\SeoAltText\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Bss\SeoAltText\Helper\Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    /**
     * @return int
     */
    public function getTotalLink()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('id');
        $collection->addAttributeToSelect('name');
        return $collection->getSize();
    }

    /**
     * @return string
     */
    public function getBackLink()
    {
        return $this->getUrl('bss_alt_text/album/dashboard');
    }

    /**
     * @return string
     */
    public function getLinkCrawl()
    {
        return $this->getUrl('bss_alt_text/process/product');
    }

    /**
     * @return string
     */
    public function getLinkAjax()
    {
        return $this->getUrl('bss_alt_text/process/getlinks');
    }

}
