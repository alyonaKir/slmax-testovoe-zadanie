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




namespace Mirasvit\ImageLazyLoad\Service;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Mirasvit\Core\Service\AbstractValidator;

class ValidationService extends AbstractValidator
{
    private $config;

    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    public function testLazyloadConflicts()
    {
        if($this->config->getValue('porto_settings/optimization/lazyload') === 0) {
            $this->addWarning('Porto theme lazyload is enabled');
        }

        if($this->config->getValue('mgstheme/general/lazy_load') === 1) {
            $this->addWarning('MGS theme lazyload is enabled');
        }

        $weltpixelConfigs = [
            'weltpixel_owl_carousel_config/new_products/lazyLoad',
            'weltpixel_owl_carousel_config/bestsell_products/lazyLoad',
            'weltpixel_owl_carousel_config/sell_products/lazyLoad',
            'weltpixel_owl_carousel_config/recently_viewed/lazyLoad',
            'weltpixel_owl_carousel_config/related_products/lazyLoad',
            'weltpixel_owl_carousel_config/upsell_products/lazyLoad',
            'weltpixel_owl_carousel_config/crosssell_products/lazyLoad',
            'weltpixel_owl_carousel_config/category_products/lazyLoad',
        ];

        $welpixelLazyEnabled = [];

        foreach ($weltpixelConfigs as $wpConfig) {
            if($this->config->getValue($wpConfig)) {
                $weltpixelLazyEnabled[] = $this->prepareWeltpixelSliderName($wpConfig);
            }
        }

        if (count($welpixelLazyEnabled)) {
            $this->addWarning(
                "Weltpixel's lazyload for sliders "
                . implode(', ', $weltpixelLazyEnabled)
                . " is enabled. Our lazyload will not be applied to these sliders"
            );
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function prepareWeltpixelSliderName($path)
    {
        $sliderName = str_replace('weltpixel_owl_carousel_config/', '', $path);
        $sliderName = str_replace( '/lazyload', '', $sliderName);
        $sliderName = str_replace('_', ' ', $sliderName);

        return ucwords($sliderName);
    }
}
