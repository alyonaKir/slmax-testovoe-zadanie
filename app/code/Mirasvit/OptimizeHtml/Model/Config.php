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



namespace Mirasvit\OptimizeHtml\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\Core\Service\SerializeService;

class Config
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getFontsToPreload()
    {
        $fonts  = [];
        $config = $this->scopeConfig->getValue(
            'mst_optimize/optimize_html/preload_fonts',
            ScopeInterface::SCOPE_STORE
        );

        $config = SerializeService::decode($config);

        if (is_array($config)) {
            foreach ($config as $item) {
                $item    = (array)$item;
                $fonts[] = $item['expression'];
            }
        }

        return $fonts;
    }
}
