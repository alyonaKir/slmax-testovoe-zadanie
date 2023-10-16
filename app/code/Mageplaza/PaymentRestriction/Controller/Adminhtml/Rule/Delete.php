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
 * @package     Mageplaza_PaymentRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PaymentRestriction\Controller\Adminhtml\Rule;

use Exception;
use Magento\Framework\Controller\Result\Redirect;
use Mageplaza\PaymentRestriction\Controller\Adminhtml\Rule;

/**
 * Class Delete
 * @package Mageplaza\PaymentRestriction\Controller\Adminhtml\Rule
 */
class Delete extends Rule
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->ruleFactory->create()
                    ->load($id)
                    ->delete();

                $this->messageManager->addSuccessMessage(__('The Rule has been deleted.'));
            } catch (Exception $e) {
                /** display error message */
                $this->messageManager->addErrorMessage($e->getMessage());
                /** go back to edit form */
                $resultRedirect->setPath('mppaymentrestriction/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        } else {
            /** display error message */
            $this->messageManager->addErrorMessage(__('Rule to delete was not found.'));
        }

        /** goto grid */
        $resultRedirect->setPath('mppaymentrestriction/*/');

        return $resultRedirect;
    }
}
