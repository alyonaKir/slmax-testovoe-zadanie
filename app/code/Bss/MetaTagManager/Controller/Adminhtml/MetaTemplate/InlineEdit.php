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
namespace Bss\MetaTagManager\Controller\Adminhtml\MetaTemplate;

/**
 * Class InlineEdit
 *
 * @package Bss\MetaTagManager\Controller\Adminhtml\MetaTemplate
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\MetaTagManager\Model\MetaTemplateFactory
     */
    protected $metaTemplateFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * InlineEdit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
    ) {
        $this->metaTemplateFactory = $metaTemplateFactory;
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
                foreach (array_keys($postItems) as $modelid) {
                    /** @var \Magento\Cms\Model\Block $block */
                    $model = $this->loadModel($modelid);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$modelid]));
                        $this->saveModel($model);
                    } catch (\Exception $e) {
                        $messages[] = "[Meta Template ID: {$modelid}]  {$e->getMessage()}";
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
     * Load Model
     *
     * @param string $id
     * @return mixed
     */
    protected function loadModel($id)
    {
        return $this->metaTemplateFactory->create()->load($id);
    }

    /**
     * Get Model collection
     *
     * @return mixed
     */
    protected function getModel()
    {
        return $this->metaTemplateFactory->create();
    }

    /**
     * Save model
     *
     * @param object $model
     * @return $this
     */
    protected function saveModel($model)
    {
        $model->save();
        return $this;
    }

    /**
     * Is Allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_MetaTagManager::meta_template');
    }
}
