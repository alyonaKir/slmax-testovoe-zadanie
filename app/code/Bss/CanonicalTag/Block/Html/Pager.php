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
namespace Bss\CanonicalTag\Block\Html;

use Magento\Framework\View\Element\Template;

/**
 * Class Pager
 *
 * @package Bss\CanonicalTag\Block\Html
 */
class Pager extends Template
{

    /**
     * @var \Magento\Framework\Registry|null
     */
    public $registry = null;

    /** @var  $catalog \Magento\Catalog\Model\Category */
    public $category = null;

    /** @var $context \Magento\Framework\View\Element\Template\Context */
    public $context = null;

    /** @var $toolbar \Magento\Catalog\Block\Product\ProductList\Toolbar; */
    public $toolbar = null;

    /** @var $pager \Magento\Theme\Block\Html\Pager */
    public $pager = null;

    /**
     * @var \Bss\CanonicalTag\Helper\Data
     */
    public $dataHelper;

    /**
     * Pager constructor.
     * @param Template\Context $context
     * @param \Bss\CanonicalTag\Helper\Data $dataHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
     * @param \Magento\Theme\Block\Html\Pager $pager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Bss\CanonicalTag\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
        \Magento\Theme\Block\Html\Pager $pager,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
        $this->context = $context;
        $this->registry = $registry;
        $this->toolbar = $toolbar;
        $this->pager = $pager;
    }

    /**
     * Get data
     *
     * @return \Bss\CanonicalTag\Helper\Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    /**
     * Get store ID
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get pager header
     *
     * @return mixed
     */
    public function getPageHeaders()
    {
        $this->category = $this->registry->registry('current_category');
        //set pager and toolbar to use same collection, make sure they're both in agreement as to page limiting
        $this->toolbar->setCollection($this->category->getProductCollection());
        $tool = $this->pager->setCollection($this->category->getProductCollection());
        $tool->setShowPerPage($this->toolbar->getLimit());
        $hasPrevPage = $hasNextPage = false;
        $nextPageUrl = $prevPageUrl = '';

        //calculate last page, round down to nearest int, 4.9 pages still means only 4 pages.
        $lastPage =  intval((
            (
            $this->category->getProductCollection()->getSize() - 1
        ) / $this->toolbar->getLimit()
            ) + 1);
        $currentPage = $this->toolbar->getCurrentPage();

        //don't show a previous page on the first page, or if the last page is 1.
        if ($currentPage != 1 && $this->toolbar->getLastPageNum() != 1) {
            $hasPrevPage = true;
            $params = $this->getRequest()->getParams();
            //remove ?id param for category identification.
            $params['id'] = null;
            $params[$tool->getPageVarName()] = $currentPage-1;
            $prevPageUrl = $this->toolbar->getPagerUrl($params);
        }
        //if our page isn't the last page, show the next one.
        if ($currentPage != $lastPage) {
            $hasNextPage = true;
            $params = $this->getRequest()->getParams();
            //remove ?id param for category identification.
            $params['id'] = null;
            $params[$tool->getPageVarName()] = $currentPage+1;
            $nextPageUrl = $this->toolbar->getPagerUrl($params);
        }
        $result['next']['status'] = $hasNextPage;
        $result['prev']['status'] = $hasPrevPage;
        $result['next']['url'] = $nextPageUrl;
        $result['prev']['url'] = $prevPageUrl;

        return $result;
    }
}
