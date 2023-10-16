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
 * @package    Bss_SeoExternalLinks
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoExternalLinks\Plugin;

/**
 * Class ReplaceHtmlCms
 * @package Bss\SeoExternalLinks\Plugin
 */
class ReplaceHtmlCms
{
    /**
     * @var \Bss\SeoExternalLinks\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * ReplaceHtmlCms constructor.
     * @param \Bss\SeoExternalLinks\Helper\Data $dataHelper
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Bss\SeoExternalLinks\Helper\Data $dataHelper,
        \Magento\Framework\App\State $state
    ) {
        $this->state = $state;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Cms\Block\Page $subject
     * @param null $html
     * @return string|string[]|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToHtml(\Magento\Cms\Block\Page $subject, $html = null)
    {
        if (!$this->dataHelper->getModelEnable()) {
            return $html;
        }
        $areaCode = $this->state->getAreaCode();
        if ($areaCode == \Bss\SeoExternalLinks\Helper\Data::AREA_CODE) {
            return $html;
        }

        if (strpos($html, '<a') === false) {
            return $html;
        }
        $html = $this->dataHelper->addNofollow($html);
        return $html;
    }
}
