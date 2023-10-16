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
namespace Bss\XmlSiteMap\Helper;

/**
 * Class ProcessSitemap
 * @package Bss\XmlSiteMap\Helper
 */
class ProcessSitemap
{
    /**
     * @var \Bss\XmlSiteMap\Model\ResourceModel\Catalog\CategoryFactory
     */
    private $categoryFactory;
    /**
     * @var \Bss\XmlSiteMap\Model\ResourceModel\Catalog\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Bss\XmlSiteMap\Model\ResourceModel\Cms\PageFactory
     */
    private $cmsFactory;
    /**
     * @var \Bss\XmlSiteMap\Model\ResourceModel\Homepage\PageFactory
     */
    private $homeFactory;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var int
     */
    public $countEntityProduct = 0;
    /**
     * @var int
     */
    public $countEntityCms = 0;
    /**
     * @var int
     */
    public $countEntityHome = 0;
    /**
     * @var int
     */
    public $countEntityCategory = 0;

    /**
     * @var int
     */
    public $countEntityAdditional = 0;

    /**
     * ProcessSitemap constructor.
     * @param \Bss\XmlSiteMap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory
     * @param \Bss\XmlSiteMap\Model\ResourceModel\Catalog\ProductFactory $productFactory
     * @param \Bss\XmlSiteMap\Model\ResourceModel\Cms\PageFactory $cmsFactory
     * @param \Bss\XmlSiteMap\Model\ResourceModel\Homepage\PageFactory $homeFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \Bss\XmlSiteMap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Bss\XmlSiteMap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Bss\XmlSiteMap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Bss\XmlSiteMap\Model\ResourceModel\Homepage\PageFactory $homeFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->homeFactory = $homeFactory;
        $this->cmsFactory = $cmsFactory;
    }

    /**
     * @param object $helper
     * @param int $storeId
     * @return array
     */
    public function getSitemapItemCollection($helper, $storeId)
    {
        $dataReturn = [];
        $disableCmsPage = $helper->getDisablePage($storeId);
        $additionString = $helper->getAdditionString($storeId);
        $enableHomepage = $helper->getHomepageEnable($storeId);

        $cmsCollection = $this->cmsFactory->create()->getCollection($storeId, $disableCmsPage);
        $dataReturn[] = $this->dataObjectFactory->create()->addData(
            [
                'changefreq' => $helper->getPageChangefreq($storeId),
                'priority' => $helper->getPagePriority($storeId),
                'collection' => $cmsCollection,
            ]
        );
        $this->countEntityCms = count($cmsCollection);

        $categoryCollection = $this->categoryFactory->create()->getCollection($storeId);
        $dataReturn[] = $this->dataObjectFactory->create()->addData(
            [
                'changefreq' => $helper->getCategoryChangefreq($storeId),
                'priority' => $helper->getCategoryPriority($storeId),
                'collection' => $categoryCollection,
            ]
        );
        $this->countEntityCategory = count($categoryCollection);

        $productCollection = $this->productFactory->create()->getCollection($storeId);
        $dataReturn[] = $this->dataObjectFactory->create()->addData(
            [
                'changefreq' => $helper->getProductChangefreq($storeId),
                'priority' => $helper->getProductPriority($storeId),
                'collection' => $productCollection,
            ]
        );
        $this->countEntityProduct = count($productCollection);

        if ($additionString != '' || $additionString != null) {
            $additionalCollection = $this->cmsFactory->create()->getCollectionAddition($storeId, $additionString);
            $dataReturn[] = $this->dataObjectFactory->create()->addData(
                [
                    'changefreq' => $helper->getAdditionChangefreq($storeId),
                    'priority' => $helper->getAdditionPriority($storeId),
                    'collection' => $additionalCollection,
                ]
            );
            $this->countEntityAdditional = count($additionalCollection);
        }

        if ($enableHomepage == '1') {
            $homeCollection = $this->homeFactory->create()->getCollection($storeId);
            $dataReturn[] = $this->dataObjectFactory->create()->addData(
                [
                    'changefreq' => $helper->getHomepageChangefreq($storeId),
                    'priority' => $helper->getHomepagePriority($storeId),
                    'collection' => $homeCollection,
                ]
            );
            $this->countEntityHome = count($homeCollection);
        }
        return $dataReturn;
    }

    /**
     * @return array
     */
    public function getCountEntity()
    {
        $countEntity = [
            'product' => $this->countEntityProduct,
            'category' => $this->countEntityCategory,
            'cms' => $this->countEntityCms,
            'home' => $this->countEntityHome,
            'additional' => $this->countEntityAdditional
        ];
        return $countEntity;
    }
}
