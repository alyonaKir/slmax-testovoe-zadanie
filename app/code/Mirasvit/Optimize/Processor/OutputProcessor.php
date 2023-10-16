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



namespace Mirasvit\Optimize\Processor;

use Magento\Framework\App\Request\Http as Request;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;

class OutputProcessor
{
    /**
     * @var OutputProcessorInterface[]
     */
    private $pool;

    private $request;

    public function __construct(
        Request $request,
        array $pool = []
    ) {
        $this->request = $request;

        ksort($pool);

        $this->pool = $pool;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function process($content)
    {
        if (strpos($this->request->getFullActionName(), 'feed') !== false) {
            return $content;
        }

        // fix for the issue with frontend page builder
        if (preg_match('/<html[^>]*class=["\'][^>].*builder-container[^>]*>/is', $content)) {
            return $content;
        }

        foreach ($this->pool as $processor) {
            $content = $processor->process($content);
        }

        return $content;
    }
}
