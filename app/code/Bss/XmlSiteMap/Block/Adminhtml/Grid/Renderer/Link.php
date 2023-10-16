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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Block\Adminhtml\Grid\Renderer;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Link
 *
 * @package Bss\XmlSiteMap\Block\Adminhtml\Grid\Renderer
 */
class Link extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    public $filesystem;

    /**
     * @var \Bss\XmlSiteMap\Model\SitemapFactory
     */
    public $sitemapFactory;

    /**
     * @var \Bss\XmlSiteMap\Helper\Data
     */
    public $sitemapData;

    /**
     * Link constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Bss\XmlSiteMap\Model\SitemapFactory $sitemapFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Bss\XmlSiteMap\Helper\Data $sitemapData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Bss\XmlSiteMap\Model\SitemapFactory $sitemapFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Bss\XmlSiteMap\Helper\Data $sitemapData,
        array $data = []
    ) {
        $this->sitemapData = $sitemapData;
        $this->sitemapFactory = $sitemapFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /** @var $sitemap \Bss\XmlSiteMap\Model\Sitemap */
        $sitemap = $this->sitemapFactory->create();
        $url = $this->escapeHtml(
            $sitemap->getSitemapUrl($row->getData('xml_sitemap_path'), $row->getData('xml_sitemap_filename'))
        );

        $fileName = preg_replace(
            '/^\//',
            '',
            $row->getData('xml_sitemap_path') . $row->getData('xml_sitemap_filename')
        );
        $rootPath = $this->sitemapData->getRootPath();
        $fileName = $rootPath . $fileName;
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        if ($directory->isFile($fileName)) {
            return sprintf('<a href="%1$s">%1$s</a>', $url);
        }

        return $url;
    }
}
