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
 * @package    Bss_SeoToolbar
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoToolbar\Block;

/**
 * Class SeoToolbar
 * @package Bss\SeoToolbar\Block
 */
class SeoToolbar extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\SeoToolbar\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Cms\Model\Page
     */
    private $page;

    /**
     * SeoToolbar constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\SeoToolbar\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\Registry $registry,
        \Bss\SeoToolbar\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
        $this->page = $page;
        $this->storeManager = $context->getStoreManager();
        $this->request = $context->getRequest();
        parent::__construct($context, $data);
    }

    /**
     * @return \Bss\SeoToolbar\Helper\Data
     */
    public function getDataHelper()
    {
        return $this->helper;
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('seo_toolbar/index/checktoken');
    }

    /**
     * @return array
     */
    public function getEntityObject()
    {
        $fullActionName = $this->getFullActionName();
        if ($fullActionName == 'catalog_product_view') {
            $currentProduct = $this->registry->registry('current_product');
            $entityType = 'product';
            $entityId = $currentProduct->getId();
        } elseif ($fullActionName == 'catalog_category_view') {
            $currentCategory  = $this->registry->registry('current_category');
            $entityType = 'category';
            $entityId = $currentCategory->getId();
        } elseif ($fullActionName == 'cms_page_view') {
            $entityId = $this->page->getId();
            $entityType = 'cms-page';
        } else {
            $entityType = '';
            $entityId = '';
        }
        $dataReturn = [
            'entity_id' => $entityId,
            'entity_type' => $entityType
        ];
        return $dataReturn;
    }

    /**
     * @return string
     */
    public function getFullActionName()
    {
        return $this->getRequest()->getFullActionName();
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        $currentPath = $this->request->getOriginalPathInfo();
        $currentPath = ltrim($currentPath, '/');

        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);

        if ($currentPath !== null && $currentPath !== '') {
            $currentPath = strstr($url, $currentPath);
        } else {
            $currentPath = strstr($url, '?');
        }
        return $currentPath;
    }
}
