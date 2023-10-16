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
namespace Bss\XmlSiteMap\Block\Adminhtml\Grid\Renderer;

/**
 * Class EntityBreakdown
 * @package Bss\XmlSiteMap\Block\Adminhtml\Grid\Renderer
 */
class EntityBreakdown extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * EntityBreakdown constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $dataJson = $row->getData('entity_breakdown');
        $dataCount = [
            'product' => 0,
            'category' => 0,
            'cms' => 0,
            'home' => 0,
            'additional' => 0,
            'image' => 0
        ];
        if ($dataJson) {
            $dataArray = $this->jsonHelper->unserialize($dataJson);
            if (!empty($dataArray)) {
                $dataCount = $dataArray;
            }
        }
        $stringReturn = __("Categories: ") . $dataCount['category'] . ' - ' . __("Products: ") . $dataCount['product']
            . ' - ' . __("CMS Pages: ") . $dataCount['cms'] . ' - ' . __("Additional Links: ") . $dataCount['additional']
            . ' - ' . __("Images: ") . $dataCount['image'];
        return $stringReturn;
    }
}
