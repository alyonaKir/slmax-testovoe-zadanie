<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-optimize
 * @version   2.0.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\OptimizeImage\Controller\Adminhtml\Action;

use Magento\Backend\App\Action;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\OptimizeImage\Service\ResponsiveImageService;

class Cleanup extends Action
{
    protected $responsiveImageService;

    public function __construct(
        ResponsiveImageService $responsiveImageService,
        Action\Context $context
    ) {
        $this->responsiveImageService = $responsiveImageService;

        parent::__construct($context);
    }

    public function execute()
    {
        $output = [
            'success' => false,
            'message' => 'Config not saved',
        ];

        try {
            if ($this->responsiveImageService->cleanup()) {
                $output['success'] = true;
                $output['message'] = 'Removed';
            }
        } catch (\Exception $e) {
            $output['message'] = $e->getMessage();
        }

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->representJson(SerializeService::encode($output));
    }

    public function _processUrlKeys(): bool
    {
        return true;
    }
}
