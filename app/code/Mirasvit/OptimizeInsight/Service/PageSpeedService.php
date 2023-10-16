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



namespace Mirasvit\OptimizeInsight\Service;

use Mirasvit\Core\Service\SerializeService;

class PageSpeedService
{
    const STRATEGY_MOBILE  = 'mobile';
    const STRATEGY_DESKTOP = 'desktop';

    /**
     * @param string $url
     * @param string $strategy
     *
     * @return int [0...100]
     */
    public function getScore($url, $strategy)
    {
        $params = [
            'url'      => $url,
            'strategy' => $strategy,
            'locale'   => 'en_US',
        ];

        try {
            $url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?' . http_build_query($params);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);

            $data = curl_exec($ch);

            curl_close($ch);

            $data = SerializeService::decode($data);
            
            if (!isset($data['lighthouseResult'])) {
                return false;
            }

            $score = $data['lighthouseResult']['categories']['performance']['score'];

            $score = floatval($score) * 100;

            return $score;
        } catch (\Exception $e) {
            return false;
        }
    }
}
