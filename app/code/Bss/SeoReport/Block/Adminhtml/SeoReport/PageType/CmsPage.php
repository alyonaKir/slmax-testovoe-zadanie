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
namespace Bss\SeoReport\Block\Adminhtml\SeoReport\PageType;

/**
 * Class ReportField
 * @package Bss\SeoReport\Block\Adminhtml\Catalog\Product\Form
 */
class CmsPage extends \Bss\SeoReport\Block\Adminhtml\SeoReport\PageType
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPageUrl()
    {
        $cmsPageObject = $this->getCmsPage();
        if ($cmsPageObject) {
            $currentUrl = $this->getBaseUrl();
            $identifier = $cmsPageObject->getData('identifier');
            $pageUrl = $currentUrl . $identifier . '/';
            return $pageUrl;
        } else {
            return "";
        }
    }

    /**
     * @param array $object
     * @param string $type
     * @return array
     */
    public function getDataCrawl($object = [], $type = '')
    {
        $object = $this->getCmsPage();
        $type = 'cms-page';
        if (!$object || !$object->getId()) {
            return [];
        }
        return parent::getDataCrawl($object, $type);
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        $cmsPageObject = $this->getCmsPage();
        if (!$cmsPageObject) {
            return parent::getMetaData();
        } else {
            $metaTitle = $cmsPageObject->getData('meta_title');
            $metaKeyword = $cmsPageObject->getData('meta_keywords');
            $metaDescription = $cmsPageObject->getData('meta_description');
            $mainKeyword = $cmsPageObject->getData('main_keyword');
            $urlKey = $cmsPageObject->getData('identifier');
            $description = $cmsPageObject->getData('content');
        }
        return [
            "meta_title" => ($metaTitle) ? $metaTitle : '',
            "meta_description" => ($metaDescription) ? $metaDescription : '',
            "meta_keyword" => ($metaKeyword) ? $metaKeyword : '',
            "main_keyword" => ($mainKeyword) ? $mainKeyword : '',
            "url_key" => ($urlKey) ? $urlKey : '',
            "description" => ($description) ? $description : '',
        ];
    }

    /**
     * @param string $metaTitle
     * @return array
     */
    public function getTitleInfo($metaTitle = '')
    {
        $cmsPageObject = $this->getCmsPage();
        if (!$cmsPageObject) {
            return [
                'color' => "red",
                'percent' => 0
            ];
        }
        $metaTitle = $cmsPageObject->getData("meta_title");
        return parent::getTitleInfo($metaTitle);
    }

    /**
     * @param string $metaDescription
     * @return array
     */
    public function getDescriptionInfo($metaDescription = '')
    {
        $cmsPageObject = $this->getCmsPage();
        if (!$cmsPageObject) {
            return [
                'color' => "red",
                'percent' => 0
            ];
        }
        $metaDescription = $cmsPageObject->getData("meta_description");
        return parent::getDescriptionInfo($metaDescription);
    }
}
