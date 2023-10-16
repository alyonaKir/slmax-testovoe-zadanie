<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Model\OfflineShipping\ResourceModel\Carrier\Tablerate;

class RateQuery extends \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * RateQuery constructor.
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateRequest $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($request);
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        $bind = [
            ':website_id' => (int)$this->storeManager->getStore()->getId(),
            ':country_id' => $this->request->getDestCountryId(),
            ':region_id' => (int)$this->request->getDestRegionId(),
            ':postcode' => $this->request->getDestPostcode(),
        ];

        // Render condition by condition name
        if (is_array($this->request->getConditionName())) {
            $i = 0;
            foreach ($this->request->getConditionName() as $conditionName) {
                $bindNameKey = sprintf(':condition_name_%d', $i);
                $bindValueKey = sprintf(':condition_value_%d', $i);
                $bind[$bindNameKey] = $conditionName;
                $bind[$bindValueKey] = $this->request->getData($conditionName);
                $i++;
            }
        } else {
            $bind[':condition_name'] = $this->request->getConditionName();
            $bind[':condition_value'] = $this->request->getData($this->request->getConditionName());
        }

        return $bind;
    }
}
