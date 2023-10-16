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
namespace Bss\SeoReport\Model\ResourceModel\UrlRewrite;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Bss\SeoReport\Model\ResourceModel\UrlRewrite
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(
            \Bss\SeoReport\Model\UrlRewrite::class,
            \Bss\SeoReport\Model\ResourceModel\UrlRewrite::class
        );
    }

    /**
     * @return AbstractCollection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['seoReport' => $this->getTable('bss_seo_report')],
            'main_table.url_rewrite_id = seoReport.url_rewrite_id',
            ['canonical_tag', 'headings', 'images', 'open_graph', 'twitter_card', 'expired']
        );
    }
}
