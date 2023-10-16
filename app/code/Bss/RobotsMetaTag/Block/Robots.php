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
 * @package    Bss_RobotsMetaTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RobotsMetaTag\Block;

/**
 * Class Robots
 *
 * @package Bss\RobotsMetaTag\Block
 */
class Robots extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    public $cmsPage;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Robots constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Cms\Model\Page $cmsPage
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Cms\Model\Page $cmsPage,
        array $data = []
    ) {
        $this->request = $request;
        $this->cmsPage = $cmsPage;
        parent::__construct($context, $data);
    }

    /**
     * Get current URL
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrl()
    {
        return $this->_storeManager->getStore()->getCurrentUrl();
    }


    /**
     * Get store id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get current CMS page
     *
     * @return \Magento\Cms\Model\Page
     */
    public function getCurrentCms()
    {
        return $this->cmsPage;
    }

    /**
     * Check page type
     *
     * @param string $type
     * @param string $cmsKey
     * @return bool
     * @SuppressWarnings(NPathComplexity)
     */
    public function checkTypePage($type, $cmsKey = null)
    {
        if ($type == 'customer_account') {
            return $this->checkTypeSame($type);
        }
        if ($type == 'checkout') {
            return $this->checkTypeSame($type);
        }
        if ($type == 'contact') {
            return $this->checkTypeSame($type);
        }
        if ($type == 'product_compare') {
            return $this->checkTypeSame($type);
        }
        if ($type == 'rss_index_index') {
            return $this->checkTypeSame($type);
        }
        if ($type == 'catalogsearch_result') {
            return $this->checkTypeSame($type);
        }
        if ($type == 'wishlist') {
            return $this->checkTypeSame($type);
        }
        if ($type == 'cms') {
            return $this->checkTypeCms($cmsKey);
        }
    }

    /**
     * Check if the request type is the same
     *
     * @param string $type
     * @return bool
     */
    public function checkTypeSame($type)
    {
        if (strpos($this->request->getFullActionName(), $type) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check CMS page type
     *
     * @param string $cmsKey
     * @return bool
     */
    public function checkTypeCms($cmsKey)
    {
        if (strpos($this->request->getFullActionName(), 'cms') === false) {
            return false;
        } else {
            $cmsUrlKey = $this->getCurrentCms()->getIdentifier();
            if ($cmsKey === $cmsUrlKey) {
                return true;
            } else {
                return false;
            }
        }
    }
}
