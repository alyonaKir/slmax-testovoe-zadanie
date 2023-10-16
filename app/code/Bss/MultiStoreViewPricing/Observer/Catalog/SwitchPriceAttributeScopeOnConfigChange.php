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
namespace Bss\MultiStoreViewPricing\Observer\Catalog;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Observer is responsible for changing scope for all price attributes in system
 * depending on 'Catalog Price Scope' configuration parameter
 */
class SwitchPriceAttributeScopeOnConfigChange implements ObserverInterface
{
    /**
     * @var ReinitableConfigInterface
     */
    private $config;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * @param ReinitableConfigInterface $config
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        ReinitableConfigInterface $config,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        $this->config = $config;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helper = $helper;
    }

    /**
     * Change scope for all price attributes according to
     * 'Catalog Price Scope' configuration parameter value
     *
     * @param  EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->helper->isScopePrice()) {
            return;
        }

        $this->searchCriteriaBuilder->addFilter('frontend_input', 'price');
        $criteria = $this->searchCriteriaBuilder->create();

        $scope = $this->config->getValue(Store::XML_PATH_PRICE_SCOPE);

        if ($scope == 2) {
            $scope = ProductAttributeInterface::SCOPE_STORE_TEXT;
        } else {
            $scope = ($scope == Store::PRICE_SCOPE_WEBSITE)
                ? ProductAttributeInterface::SCOPE_WEBSITE_TEXT
                : ProductAttributeInterface::SCOPE_GLOBAL_TEXT;
        }

        $priceAttributes = $this->productAttributeRepository->getList($criteria)->getItems();

        /**
 * @var ProductAttributeInterface $priceAttribute
*/
        foreach ($priceAttributes as $priceAttribute) {
            $priceAttribute->setScope($scope);
            $this->productAttributeRepository->save($priceAttribute);
        }
    }
}
