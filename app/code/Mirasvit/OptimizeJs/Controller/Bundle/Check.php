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


namespace Mirasvit\OptimizeJs\Controller\Bundle;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\OptimizeJs\Api\Repository\BundleFileRepositoryInterface;

class Check extends Action
{
    private $bundleFileRepository;

    public function __construct(
        BundleFileRepositoryInterface $bundleFileRepository,
        Context $context
    ) {
        $this->bundleFileRepository = $bundleFileRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        $layout = $this->getRequest()->getParam('layout', '');
        $locale = $this->getRequest()->getParam('locale', 'en_US');
        $theme  = $this->getRequest()->getParam('theme', 'Magento/luma');

        $exist = $this->bundleFileRepository
            ->getCollection()
            ->addFieldToSelect('filename')
            ->addFieldToFilter('layout', $layout)
            ->addFieldToFilter('locale', $locale)
            ->addFieldToFilter('theme', $theme)
            ->addFieldToFilter('area', ['in' => ['frontend', 'base']]);

        $collectedFiles = [];

        foreach ($exist as $file) {
            $collectedFiles[] = str_replace('.js', '', $file->getFilename());
        }

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->representJson(SerializeService::encode([
            'success'   => true,
            'collected' => $collectedFiles
        ]));
    }
}
