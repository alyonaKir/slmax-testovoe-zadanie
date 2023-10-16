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
namespace Bss\MetaTagManager\Block\Adminhtml;

/**
 * Class Generate
 * @package Bss\MetaTagManager\Block\Adminhtml
 */
class Generate extends \Magento\Backend\Block\Template
{
    /**
     * @var \Bss\MetaTagManager\Model\MetaTemplateFactory
     */
    protected $metaTemplateFactory;

    /**
     * @var \Bss\MetaTagManager\Model\RuleFactory
     */
    private $ruleFactory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * Generate2 constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
     * @param \Bss\MetaTagManager\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory,
        \Bss\MetaTagManager\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->ruleFactory = $ruleFactory;
        $this->metaTemplateFactory = $metaTemplateFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param array $data
     * @return bool|false|string
     */
    public function jsonEncode(array $data)
    {
        return $this->jsonHelper->serialize($data);
    }

    /**
     * @return mixed
     */
    public function getMetaTemplateId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return int
     */
    public function getTotalLink()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->metaTemplateFactory->create();
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            $dataReturn = $model->getData();
            $modelRule = $this->ruleFactory->create();
            $modelRule->loadPost($dataReturn);
            $productCollection = $modelRule->getProductCollection();
            return $productCollection->getSize();
        }
        return 0;
    }

    /**
     * @return string
     */
    public function getSeoReportLink()
    {
        return $this->getUrl('bss_metatagmanager/metatemplate');
    }

    /**
     * @return string
     */
    public function getLinkCrawl()
    {
        return $this->getUrl('bss_metatagmanager/generate/generatemeta');
    }

    /**
     * @return string
     */
    public function getLinkAjax()
    {
        return $this->getUrl('bss_metatagmanager/generate/getlinks');
    }

}
