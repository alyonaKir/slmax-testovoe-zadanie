<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Controller\Products;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\AutoRelated\Helper\Rule;

/**
 * Class View
 * @package Mageplaza\AutoRelated\Controller\Products
 */
class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Rule
     */
    protected $ruleHelper;

    /**
     * SeeAll constructor.
     *
     * @param PageFactory $pageFactory
     * @param Rule $ruleHelper
     * @param Context $context
     */
    public function __construct(
        PageFactory $pageFactory,
        Rule $ruleHelper,
        Context $context
    ) {
        $this->pageFactory = $pageFactory;
        $this->ruleHelper  = $ruleHelper;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $page        = $this->pageFactory->create();
        $currentRule = $this->ruleHelper->getCurrentRule();
        $title       = $currentRule->getId() ? $currentRule->getBlockName() : 'Related Products';
        $page->getConfig()->getTitle()->set(__($title));

        $collection         = $this->ruleHelper->getProductCollection();
        $listRelateProducts = $page->getLayout()->getBlock('autorelated.products.list');
        $listRelateProducts->setProductCollection($collection);

        return $page;
    }
}
