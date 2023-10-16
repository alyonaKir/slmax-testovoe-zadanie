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
namespace Bss\SeoReport\Helper;

use Magento\Backend\App\Action\Context;

/**
 * Class StringProcess
 * @package Bss\SeoReport\Helper
 */
class SeoReportHelper
{
    /**
     * @var \Bss\SeoReport\Model\SeoReportFactory
     */
    protected $seoReportFactory;

    /**
     * @var \Bss\SeoReport\Model\ResourceModel\SeoReport
     */
    protected $seoReportModel;

    /**
     * SeoReportHelper constructor.
     * @param \Bss\SeoReport\Model\SeoReportFactory $seoReportFactory
     * @param \Bss\SeoReport\Model\ResourceModel\SeoReport $seoReportModel
     */
    public function __construct(
        \Bss\SeoReport\Model\SeoReportFactory $seoReportFactory,
        \Bss\SeoReport\Model\ResourceModel\SeoReport $seoReportModel
    ) {
        $this->seoReportModel = $seoReportModel;
        $this->seoReportFactory = $seoReportFactory;
    }

    /**
     * @param array $dataInsert
     * @return bool
     */
    public function handleData($dataInsert)
    {
        if (isset($dataInsert['url_rewrite_id'])) {
            $urlRewriteId = $dataInsert['url_rewrite_id'];

            $collection = $this->seoReportFactory->create()
                ->getCollection()
                ->addFieldToFilter('url_rewrite_id', $urlRewriteId);
            if ($collection->getSize()) {
                //Update
                $this->seoReportModel->updateData($dataInsert, $urlRewriteId);
            } else {
                //Insert
                $this->seoReportModel->insertData($dataInsert);
            }
            return true;
        } else {
            return false;
        }
    }
}
