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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Price
 */
class ProcessColumn extends Column
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Bss\SeoReport\Helper\ItemProcess
     */
    private $itemProcess;

    /**
     * ProcessColumn constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\SeoReport\Helper\ItemProcess $itemProcess
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\SeoReport\Helper\ItemProcess $itemProcess,
        array $components = [],
        array $data = []
    ) {
        $this->itemProcess = $itemProcess;
        $this->storeManager = $storeManager;
        $this->context = $context;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $baseUrlObject = [];
            foreach ($dataSource['data']['items'] as & $item) {
                $storeId = $item['store_id'];
                if (!isset($baseUrlObject[$storeId])) {
                    $baseUrlObject[$storeId] = $this->getStoreUrl($storeId);
                }
                $baseUrl = $baseUrlObject[$storeId];
                $urlPath = ltrim($item['request_path'], "/");
                $item['request_path'] = $baseUrl . $urlPath;
                $entityId = $item['entity_id'];
                $entityType =  $item['entity_type'];

                if ($entityType === 'product' || $entityType === 'category') {
                    $backendUrl = $this->context->getUrl('catalog/' . $entityType . '/edit/id/' . $entityId);
                } else {
                    $backendUrl = $this->context->getUrl('cms/page/edit/page_id/' . $entityId);
                }

                $item = $this->itemProcess->processEntityMeta($item, $entityType, $entityId);

                $item = $this->itemProcess->processEntityTag($item);
                $item = $this->itemProcess->processEntityImages($item);
                $item = $this->itemProcess->processHeadings($item);
                if ((int)$item['status'] === 1) {
                    $item['url_action'] = '<a href="' . $backendUrl . '" target="_blank">' . __('Edit') . '</a> | <a href="' .
                        $item['request_path'] . '" target="_blank">' . __('View') . '</a>';
                } else {
                    $item['url_action'] = '<a href="' . $backendUrl . '" target="_blank">' . __('Edit') . '</a>';
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreUrl($storeId)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
    }
}
