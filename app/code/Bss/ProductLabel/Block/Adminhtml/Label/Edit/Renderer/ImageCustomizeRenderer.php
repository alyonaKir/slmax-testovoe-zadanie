<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Block\Adminhtml\Label\Edit\Renderer;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ImageCustomizeRenderer
 * Render a canvas for customizing label position/size
 * @package Bss\ProductLabel\Block\Adminhtml\Label\Edit\Renderer
 */
class ImageCustomizeRenderer extends AbstractElement implements \Magento\Framework\View\Element\BlockInterface
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * ImageCustomizeRenderer constructor.
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        array $data = []
    ) {
        $this->layoutFactory = $layoutFactory;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Render image customize template
     * @return string
     */
    public function getElementHtml()
    {
        $block = $this->layoutFactory->create()
            ->createBlock(\Bss\ProductLabel\Block\Adminhtml\Label\Edit\ImageCustomize::class)
            ->setTemplate('Bss_ProductLabel::form/imageCustomize.phtml')
            ->toHtml();
        return $block;
    }
}
