<?php
namespace Bss\MultiStoreViewPricing\Plugin\Quote\ResourceModel\Item;

class Collection
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\Collection $subject
     * @param int $result
     * @return int
     */
    public function afterGetStoreId($subject, $result)
    {
        if (!$this->helper->isScopePrice()) {
            return $result;
        }

        $quote = $subject->getFirstItem()->getQuote();
        if (!$quote) {
            return $result;
        }

        return $subject->getFirstItem()->getQuote()->getStoreId();
    }
}
