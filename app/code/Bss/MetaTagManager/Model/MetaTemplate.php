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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Model;

use Bss\MetaTagManager\Api\Data\MetaTemplateInterface;

/**
 * Class MetaTemplate
 * @package Bss\MetaTagManager\Model
 */
class MetaTemplate extends \Magento\Framework\Model\AbstractModel implements MetaTemplateInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\MetaTagManager\Model\ResourceModel\MetaTemplate::class);
    }

    /**
     * Get id
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get category
     * @return string
     */
    public function getCategory()
    {
        return $this->getData(self::CATEGORY);
    }

    /**
     * Set category
     * @param string $category
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setCategory($category)
    {
        return $this->setData(self::CATEGORY, $category);
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get store
     * @return string
     */
    public function getStore()
    {
        return $this->getData(self::STORE);
    }

    /**
     * Get store
     * @return array
     */
    public function getStoreArray()
    {
        return explode(',', $this->getData(self::STORE));
    }

    /**
     * Set store
     * @param string $store
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setStore($store)
    {
        return $this->setData(self::STORE, $store);
    }

    /**
     * Get priority
     * @return string
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    /**
     * Set priority
     * @param string $priority
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * Get replace_meta_data
     * @return string
     */
    public function getReplaceMetaData()
    {
        return $this->getData(self::REPLACE_META_DATA);
    }

    /**
     * Set replace_meta_data
     * @param string $replaceMetaData
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setReplaceMetaData($replaceMetaData)
    {
        return $this->setData(self::REPLACE_META_DATA, $replaceMetaData);
    }

    /**
     * Get replace_description
     * @return string
     */
    public function getReplaceDescription()
    {
        return $this->getData(self::REPLACE_DESCRIPTION);
    }

    /**
     * Set replace_description
     * @param string $replaceMetaDescription
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setReplaceDescription($replaceMetaDescription)
    {
        return $this->setData(self::REPLACE_DESCRIPTION, $replaceMetaDescription);
    }

    /**
     * Get use_sub
     * @return string
     */
    public function getUseSub()
    {
        return $this->getData(self::USE_SUB);
    }

    /**
     * Set use_sub
     * @param string $useSub
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setUseSub($useSub)
    {
        return $this->setData(self::USE_SUB, $useSub);
    }

    /**
     * Get meta_title
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * Set meta_title
     * @param string $metaTitle
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * Get meta_description
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Set meta_description
     * @param string $metaDescription
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Get meta_keyword
     * @return string
     */
    public function getMetaKeyword()
    {
        return $this->getData(self::META_KEYWORD);
    }

    /**
     * Set meta_keyword
     * @param string $metaKeywordBss
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setMetaKeyword($metaKeywordBss)
    {
        return $this->setData(self::META_KEYWORD, $metaKeywordBss);
    }

    /**
     * Get short_description
     * @return string
     */
    public function getShortDescription()
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /**
     * Set short_description
     * @param string $shortDescription
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setShortDescription($shortDescription)
    {
        return $this->setData(self::SHORT_DESCRIPTION, $shortDescription);
    }

    /**
     * Get full_description
     * @return string
     */
    public function getFullDescription()
    {
        return $this->getData(self::FULL_DESCRIPTION);
    }

    /**
     * Set full_description
     * @param string $fullDescription
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setFullDescription($fullDescription)
    {
        return $this->setData(self::FULL_DESCRIPTION, $fullDescription);
    }
}
