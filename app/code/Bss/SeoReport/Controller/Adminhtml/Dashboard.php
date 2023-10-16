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
namespace Bss\SeoReport\Controller\Adminhtml;

/**
 * Class Dashboard
 * @package Bss\SeoReport\Controller\Adminhtml
 */
abstract class Dashboard extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Bss_Breadcrumbs::top_level';

    /**
     * Init page
     *
     * @param string $resultPage
     * @return mixed
     */
    public function initPage($resultPage)
    {
        $resultPage->addBreadcrumb(__('Bss'), __('Bss'))
            ->addBreadcrumb(__('SEO Report Dashboard'), __('SEO Report Dashboard'));
        return $resultPage;
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_SeoReport::seo_report');
    }
}
