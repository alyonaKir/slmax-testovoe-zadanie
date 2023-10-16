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

/**
 * Class Delete
 * @package Bss\ProductLabel\Controller\Adminhtml\Label
 */
class Delete extends Label
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $labelId = (int) $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($labelId) {
            $labelModel = $this->labelFactory->create();
            $labelModel->load($labelId);

            // Check model exists or not
            if (!$labelModel->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
            } else {
                try {
                    // Delete label
                    $labelModel->delete();
                    $this->messageManager->addSuccessMessage(__('The item has been deleted.'));

                    // Redirect to grid page
                    return $resultRedirect->setPath('*/*/');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/*/edit', ['id' => $labelModel->getId()]);
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ProductLabel::delete_label');
    }
}
