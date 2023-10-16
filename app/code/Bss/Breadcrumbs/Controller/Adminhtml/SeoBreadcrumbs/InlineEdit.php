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
 * @package    Bss_Breadcrumbs
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Breadcrumbs\Controller\Adminhtml\SeoBreadcrumbs;

use Bss\Breadcrumbs\Model\ResourceModel\Path;

/**
 * Class InlineEdit
 *
 * @package Bss\Breadcrumbs\Controller\Adminhtml\SeoBreadcrumbs
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $myObjectManager;

    /**
     * @var Path
     */
    protected $pathSave;

    /**
     * InlineEdit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param Path $pathSave
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        Path $pathSave
    ) {
        $this->myObjectManager = $context->getObjectManager();
        $this->pathSave = $pathSave;
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (empty($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $modelId) {
                    /** @var \Magento\Cms\Model\Block $block */
                    $model = $this->loadModel($modelId);
                    try {
                        $dataHandle = $this->handleUpdateBreadcrumbsPriority($model, $postItems, $modelId);
                        $messages[] = $dataHandle['message'];
                        $error = $dataHandle['error'];
                    } catch (\Exception $e) {
                        $messages[] = "[Path ID False: {$modelId}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Handle the breadcrumbs
     *
     * @param \Bss\Breadcrumbs\Model\Path $model
     * @param array $postItems
     * @param int $modelId
     * @return mixed
     */
    protected function handleUpdateBreadcrumbsPriority($model, $postItems, $modelId)
    {
        $dataReturn['error'] = false;
        $dataReturn['message'] = false;
        $path = $model->getData();
        $path = '/' . $path['path'] . '/';
        $myPath = '/' . $postItems[$modelId]['priority_id'] . '/';
        $searchPath = strpos($path, $myPath);
        if ($searchPath) {
            $this->pathSave->update(
                $postItems[$modelId]['priority_id'],
                $postItems[$modelId]['entity_id']
            );
        } else {
            if ($myPath == "//") {
                $this->pathSave->update(
                    $postItems[$modelId]['priority_id'],
                    $postItems[$modelId]['entity_id']
                );
            } else {
                $dataReturn['message'] = "Please choose Category ID in the Path";
                $dataReturn['error'] = true;
            }
        }
        return $dataReturn;
    }

    /**
     * Load model
     *
     * @param string $modelId
     * @return mixed
     */
    protected function loadModel($modelId)
    {
        return $this->myObjectManager->create('Bss\Breadcrumbs\Model\Path')->load($modelId);
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Breadcrumbs::seobreadcrumbs');
    }
}
