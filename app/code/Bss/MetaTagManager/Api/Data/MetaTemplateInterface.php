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
namespace Bss\MetaTagManager\Api\Data;

/**
 * Interface MetaTemplateInterface
 * @package Bss\MetaTagManager\Api\Data
 */
interface MetaTemplateInterface
{
    const SHORT_DESCRIPTION = 'short_description';
    const NAME = 'name';
    const ID = 'id';
    const META_TITLE = 'meta_title';
    const META_DESCRIPTION = 'meta_description';
    const CATEGORY = 'category';
    const STATUS = 'status';
    const META_KEYWORD = 'meta_keyword';
    const PRIORITY = 'priority';
    const STORE = 'store';
    const FULL_DESCRIPTION = 'full_description';
    const USE_SUB = 'use_sub';
    const REPLACE_META_DATA = 'replace_meta_data';
    const REPLACE_DESCRIPTION = 'replace_description';

    /**
     * Get id
     *
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param string $id
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setId($id);

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setName($name);

    /**
     * Get category
     *
     * @return string|null
     */
    public function getCategory();

    /**
     * Set category
     *
     * @param string $category
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setCategory($category);

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setStatus($status);

    /**
     * Get store
     *
     * @return string|null
     */
    public function getStore();

    /**
     * Set store
     *
     * @param string $store
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setStore($store);

    /**
     * Get priority
     *
     * @return string|null
     */
    public function getPriority();

    /**
     * Set priority
     *
     * @param string $priority
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setPriority($priority);

    /**
     * Get replace_meta_data
     *
     * @return string|null
     */
    public function getReplaceMetaData();

    /**
     * Set replace_meta_data
     *
     * @param string $replaceMetaData
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setReplaceMetaData($replaceMetaData);

    /**
     * Get replace_description
     *
     * @return string|null
     */
    public function getReplaceDescription();

    /**
     * Set replace_description
     *
     * @param string $replaceMetaDescription
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setReplaceDescription($replaceMetaDescription);

    /**
     * Get use_sub
     *
     * @return string|null
     */
    public function getUseSub();

    /**
     * Set use_sub
     *
     * @param string $useSub
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setUseSub($useSub);

    /**
     * Get meta_title
     *
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * Set meta_title
     *
     * @param string $metaTitle
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get meta_description
     *
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * Set meta_description
     *
     * @param string $metaDescription
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get meta_keyword
     *
     * @return string|null
     */
    public function getMetaKeyword();

    /**
     * Set meta_keyword
     *
     * @param string $metaKeywordBss
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setMetaKeyword($metaKeywordBss);

    /**
     * Get short_description
     *
     * @return string|null
     */
    public function getShortDescription();

    /**
     * Set short_description
     *
     * @param string $shortDescription
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setShortDescription($shortDescription);

    /**
     * Get full_description
     *
     * @return string|null
     */
    public function getFullDescription();

    /**
     * Set full_description
     *
     * @param string $fullDescription
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     */
    public function setFullDescription($fullDescription);
}
