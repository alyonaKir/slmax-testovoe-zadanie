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
namespace Bss\ProductLabel\Controller\Adminhtml\Label;

use Bss\ProductLabel\Controller\Adminhtml\Label;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Bss\ProductLabel\Model\LabelFactory;

/**
 * Class Edit
 * @package Bss\ProductLabel\Controller\Adminhtml\Label
 */
class Edit extends Label
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $labelId = $this->getRequest()->getParam('id');
        $model = $this->labelFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($labelId) {
            $model->load($labelId);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        // Restore previously entered form data from session
        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $model->getConditions()->setJsFormObject('label_conditions_fieldset');
        $this->coreRegistry->register('productlabel_label', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bss_ProductLabel::productLabel');
        $resultPage->getConfig()->getTitle()->prepend(__('Product Label'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ProductLabel::edit_label');
    }
}
