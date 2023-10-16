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
 * @package     Mageplaza_ShippingRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRestriction\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Store
 * @package Mageplaza\ShippingRestriction\Ui\Component\Listing\Columns
 */
class Store extends Column
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $_escaper;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $_systemStore;

    /**
     * Store manager
     *
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * @var string
     */
    protected $_storeKey;

    /**
     * Store constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @param StoreManager $storeManager
     * @param array $components
     * @param array $data
     * @param string $storeKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        StoreManager $storeManager,
        array $components = [],
        array $data = [],
        $storeKey = 'store_ids'
    ) {
        $this->_systemStore  = $systemStore;
        $this->_escaper      = $escaper;
        $this->_storeKey     = $storeKey;
        $this->_storeManager = $storeManager;

        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            /** @var array [][] $item */
            foreach ($dataSource['data']['items'] as & $item) {
                $name                         = $this->getData('name');
                $item[$name]                  = explode(',', $item[$name]);
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     *
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $content    = '';
        $origStores = $item[$this->_storeKey];
        if (!is_array($origStores)) {
            $origStores = [$origStores];
        }
        if (in_array('0', $origStores, true)) {
            return __('All Store Views');
        }
        $data = $this->_systemStore->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            $content .= '<b>' . $website['label'] . '</b><br/>';
            /** @var array[] $group */
            foreach ($website['children'] as $group) {
                $content .= str_repeat('&nbsp;', 3) . '<b>' . $this->_escaper->escapeHtml($group['label']);
                $content .= '</b><br/>';
                foreach ($group['children'] as $store) {
                    $content .= str_repeat('&nbsp;', 6) . $this->_escaper->escapeHtml($store['label']) . '<br/>';
                }
            }
        }

        return $content;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->_storeManager->isSingleStoreMode()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
