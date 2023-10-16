<?php

namespace Bss\PriceCustomize\Plugin\Block\Product\View\Type;

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class Configurable
 *
 * @package Bss\PriceCustomize\Plugin\Block\Product\View\Type
 */
class Configurable
{
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * Configurable constructor.
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Plugin modify returned price
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string;
     */
    public function afterGetJsonConfig($subject, $result)
    {
        $product = $subject->getProduct();
        $isCustomize = $product->getIsCustomize();
        if ($isCustomize) {
            $config = $this->jsonDecoder->decode($result);
            if ($product->getCustomSpecialPrice()) {
                $config['prices'] = [
                    'oldPrice'   => [
                        'amount'      => (float)$product->getNormalPrice(),
                        'adjustments' => []
                    ],
                    'basePrice'  => [
                        'amount'      => (float)$product->getCustomSpecialPrice(),
                        'adjustments' => []
                    ],
                    'finalPrice' => [
                        'amount'      => (float)$product->getCustomSpecialPrice(),
                        'adjustments' => []
                    ]
                ];
            } else {
                $config['prices'] = [
                    'oldPrice'   => [
                        'amount'      => (float)$product->getNormalPrice(),
                        'adjustments' => []
                    ],
                    'basePrice'  => [
                        'amount'      => (float)$product->getNormalPrice(),
                        'adjustments' => []
                    ],
                    'finalPrice' => [
                        'amount'      => (float)$product->getNormalPrice(),
                        'adjustments' => []
                    ]
                ];
            }
            return $this->jsonEncoder->encode($config);
        }
        return $result;
    }
}
