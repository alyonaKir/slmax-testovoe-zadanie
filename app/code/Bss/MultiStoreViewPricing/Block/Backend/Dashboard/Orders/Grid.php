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
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Block\Backend\Dashboard\Orders;

class Grid extends \Magento\Backend\Block\Dashboard\Orders\Grid
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::dashboard/grid.phtml';

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $moduleManager, $collectionFactory, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        if (!$this->helper->isScopePrice()) {
            return parent::_prepareCollection();
        }

        if (!$this->_moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }
        $collection = $this->_collectionFactory->create()->addItemCountExpr()->joinCustomerName(
            'customer'
        )->orderByCreatedAt();

        if ($this->getParam('store') || $this->getParam('website') || $this->getParam('group')) {
            if ($this->getParam('store')) {
                $collection->addAttributeToFilter('store_id', $this->getParam('store'));
            } elseif ($this->getParam('website')) {
                $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', ['in' => $storeIds]);
            } elseif ($this->getParam('group')) {
                $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', ['in' => $storeIds]);
            }
        }

        $collection->addRevenueToSelect();
        $this->setCollection($collection);

        return \Magento\Backend\Block\Dashboard\Grid::_prepareCollection();
    }

    /**
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        if (!$this->helper->isScopePrice()) {
            return $result;
        }

        $this->removeColumn('total');
        $this->addColumn(
            'total',
            [
                'header' => __('Total'),
                'sortable' => false,
                'renderer' => 'Bss\MultiStoreViewPricing\Block\Backend\Dashboard\Orders\Grid\Column\Total',
                'index' => 'revenue'
            ]
        );

        return \Magento\Backend\Block\Dashboard\Grid::_prepareColumns();
    }
}
