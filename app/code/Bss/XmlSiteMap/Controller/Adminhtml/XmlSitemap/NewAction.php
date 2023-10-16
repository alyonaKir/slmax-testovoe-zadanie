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
namespace Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap;

/**
 * Class NewAction
 *
 * @package Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class NewAction extends \Bss\XmlSiteMap\Controller\Adminhtml\XmlSitemap
{
    /**
     * Create new sitemap
     *
     * @return void
     */
    public function execute()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_XmlSiteMap::bss_sitemap');
    }
}
