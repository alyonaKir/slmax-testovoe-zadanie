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
namespace Bss\MetaTagManager\Api;

/**
 * Interface MetaTemplateRepositoryInterface
 * @package Bss\MetaTagManager\Api
 */
interface MetaTemplateRepositoryInterface
{
    /**
     * Save meta_template
     *
     * @param \Bss\MetaTagManager\Api\Data\MetaTemplateInterface $metaTemplate
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        Data\MetaTemplateInterface $metaTemplate
    );

    /**
     * Retrieve meta_template
     *
     * @param string $id
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve meta_template matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Bss\MetaTagManager\Api\Data\MetaTemplateSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete meta_template
     *
     * @param \Bss\MetaTagManager\Api\Data\MetaTemplateInterface $metaTemplate
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        Data\MetaTemplateInterface $metaTemplate
    );

    /**
     * Delete meta_template by ID
     *
     * @param string $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
