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

namespace Mirasvit\OptimizeImage\Block;


use Magento\Framework\View\Element\Template;
use Mirasvit\OptimizeImage\Model\ConfigProvider;

class Debug extends Template
{
    private $configProvider;

    public function __construct(ConfigProvider $configProvider, Template\Context $context, array $data = [])
    {
        $this->configProvider = $configProvider;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        if ($this->configProvider->isDebug()) {
            $this->pageConfig->addPageAsset('Mirasvit_OptimizeImage::css/debug.css');
        }
    }
}
