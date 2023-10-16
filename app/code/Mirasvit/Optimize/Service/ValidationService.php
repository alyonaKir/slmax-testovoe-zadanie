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



namespace Mirasvit\Optimize\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Mirasvit\Core\Service\AbstractValidator;

class ValidationService extends AbstractValidator
{
    const KNOWN = [
        'Amasty_PageSpeedOptimizer',
        'Apptrian_Minify',
    ];

    private $moduleManager;

    private $moduleList;

    private $config;

    public function __construct(
        Manager $moduleManager,
        ModuleListInterface $moduleList,
        ScopeConfigInterface $config
    ) {
        $this->moduleManager          = $moduleManager;
        $this->moduleList             = $moduleList;
        $this->config                 = $config;
    }

    public function testKnownConflicts()
    {
        foreach (self::KNOWN as $moduleName) {
            if ($this->moduleManager->isEnabled($moduleName)) {
                $this->addError('Please disable {0} module.', [$moduleName]);
            }
        }
    }

    public function testPossibleConflicts()
    {
        foreach ($this->moduleList->getAll() as $module) {
            $moduleName = $module['name'];

            if (in_array($moduleName, self::KNOWN)) {
                continue;
            }

            if (stripos($moduleName, 'mirasvit') !== false) {
                continue;
            }

            if (stripos($moduleName, 'magento') !== false) {
                continue;
            }

            if (stripos($moduleName, 'optimize') !== false && $this->moduleManager->isEnabled($moduleName)) {
                $this->addWarning("Possible conflict with {0} module.", [$moduleName]);
            }
        }
    }
}
