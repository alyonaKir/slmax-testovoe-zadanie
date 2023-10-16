<?php
namespace Bss\CustomSnippets\Helper;

use Magento\Framework\Phrase;

class SnippetHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $attributes = [];

    public function getSnippetAttribute($product, $code)
    {
        $attributes = $this->getAttributes($product);
        foreach ($attributes as $key => $attribute) {
            if ($attribute->getAttributeCode() === $code) {
                if ($product->getData($code)) {
                    $value = $attribute->getFrontend()->getValue($product);
                    if ($value instanceof Phrase) {
                        $value = (string)$value;
                    }

                    if (is_string($value) && strlen($value)) {
                        return $value;
                    }
                }
                return null;
            }
        }
        return null;
    }

    private function getAttributes($product)
    {
        if (!isset($this->attributes[$product->getId()])) {
            $this->attributes[$product->getId()] = $product->getAttributes();
        }
        return $this->attributes[$product->getId()];
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
}
