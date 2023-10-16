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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Block\Product;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;

class ReviewRenderer extends \Magento\Review\Block\Product\ReviewRenderer
{
    /**
     * @var \Bss\RichSnippets\Helper\Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $_availableTemplates;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * ReviewRenderer constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Bss\RichSnippets\Helper\Data $helper
     * @param array $availableTemplates
     * @param $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Bss\RichSnippets\Helper\Data $helper,
        array $availableTemplates = [
            self::FULL_VIEW => 'helper/summary.phtml',
            self::SHORT_VIEW => 'Magento_Review::helper/summary_short.phtml',
        ],
        array $data = []
    ) {
        $this->_availableTemplates = $availableTemplates;
        $this->helper = $helper;
        $this->reviewFactory = $reviewFactory;
        parent::__construct($context, $reviewFactory, $data);
    }

    /**
     * @return \Bss\RichSnippets\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
