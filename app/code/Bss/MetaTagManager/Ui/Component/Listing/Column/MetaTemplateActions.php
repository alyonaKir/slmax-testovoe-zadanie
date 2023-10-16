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
namespace Bss\MetaTagManager\Ui\Component\Listing\Column;

/**
 * Class MetaTemplateActions
 *
 * @package Bss\MetaTagManager\Ui\Component\Listing\Column
 */
class MetaTemplateActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    const URL_PATH_DETAILS = 'bss_metatagmanager/metatemplate/details';
    const URL_PATH_EDIT = 'bss_metatagmanager/metatemplate/edit';
    const URL_PATH_DELETE = 'bss_metatagmanager/metatemplate/delete';
    const URL_PATH_GENERATE = 'bss_metatagmanager/metatemplate/generate';

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $item['id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'generate' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_GENERATE,
                                [
                                    'id' => $item['id']
                                ]
                            ),
                            'label' => __('Generate')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $item['id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete Template'),
                                'message' => __('Are you sure you want to delete this template?')
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
