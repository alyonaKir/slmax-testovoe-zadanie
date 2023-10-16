<?php

namespace Bss\PriceCustomize\Plugin\Block\Product;

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class View
 *
 * @package Bss\PriceCustomize\Plugin\Block\Product
 */
class View
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
     * @inheritDoc
     *
     * @param \Magento\Catalog\Block\Product\View $subject
     * @param string $result
     */
    public function afterGetJsonConfig($subject, $result)
    {
        $product = $subject->getProduct();
        $isCustomize = $product->getIsCustomize();
        if ($isCustomize && $product->getTypeId() == 'configurable') {
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
