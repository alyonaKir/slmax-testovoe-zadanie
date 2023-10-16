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
 * @package    Bss_Breadcrumbs
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Breadcrumbs\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\Breadcrumbs\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEO_SUITE_BREADCRUMBS_ENABLE = 'bss_breadcrumbs/general/enable';

    const SEO_SUITE_BREADCRUMBS_PRIORITY = 'bss_breadcrumbs/breadcrumbs/use_priority';

    const SEO_SUITE_BREADCRUMBS_TYPE = 'bss_breadcrumbs/breadcrumbs/type';

    /**
     * Get config enable breadcrumbs
     *
     * @param string $storeId
     * @return mixed
     */
    public function getBreadcrumbsEnable($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEO_SUITE_BREADCRUMBS_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get breadcrumbs type
     *
     * @param string $storeId
     * @return mixed
     */
    public function getBreadcrumbsType($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEO_SUITE_BREADCRUMBS_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get breadcrumbs priority
     *
     * @param string $storeId
     * @return mixed
     */
    public function getBreadcrumbsPriority($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SEO_SUITE_BREADCRUMBS_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
