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



namespace Mirasvit\OptimizeJs\Plugin;

class ConfigPlugin
{
    /**
     * @param \Magento\Config\Model\Config $config
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundSave(
        \Magento\Config\Model\Config $config,
        \Closure $proceed
    ) {
        if ($config->getData('section') == 'mst_optimize') {
            $data = $config->getData('groups');

            if (isset($data['optimize_js'])) {
                $fieldsData = $data['optimize_js']['fields'];

                $isEnabled = isset($fieldsData['enabled']) ? $fieldsData['enabled']['value'] : 0;

                if (!$isEnabled) {
                    $fieldsData['minify_js']['value'] = 0;
                }

                $data['optimize_js']['fields'] = $fieldsData;
                $config->setData('groups', $data);
            }
        }
        return $proceed();
    }
}
