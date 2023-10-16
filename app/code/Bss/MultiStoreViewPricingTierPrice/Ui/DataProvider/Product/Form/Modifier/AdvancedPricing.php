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
 * @package    Bss_MultiStoreViewPricingTierPrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingTierPrice\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Customer\Model\Customer\Source\GroupSourceInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class AdvancedPricing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedPricing extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AdvancedPricing
{
    const CODE_TIER_PRICE_FOR_STORE = 'tier_price_for_store';

	/**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::modifyMeta($meta);
        }
        
    	parent::modifyMeta($meta);
        $this->customizeTierPriceStore();

        return $this->meta;
    }

    /**
     * Customize tier price field
     *
     * @return $this
     */
    protected function customizeTierPriceStore()
    {
        $tierPricePath = $this->arrayManager->findPath(
            'tier_price_for_store',
            $this->meta,
            null,
            'children'
        );

        if ($tierPricePath) {
            $this->meta = $this->arrayManager->merge(
                $tierPricePath,
                $this->meta,
                $this->getTierPriceStoreStructure($tierPricePath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($tierPricePath, 0, -3)
                . '/' . 'tier_price_for_store',
                $this->meta,
                $this->arrayManager->get($tierPricePath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($tierPricePath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }

    /**
     * Get tier price dynamic rows structure
     *
     * @param string $tierPricePath
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getTierPriceStoreStructure($tierPricePath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Tier Price For Store View'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($tierPricePath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'store_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'store_id',
                                        'label' => __('Store View'),
                                        'options' => $this->getStoreViews(),
                                    ],
                                ],
                            ],
                        ],
                        'cust_group' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'dataScope' => 'cust_group',
                                        'label' => __('Customer Group'),
                                        'options' => $this->getCustomerGroups(),
                                        'value' => $this->getDefaultCustomerGroup(),
                                    ],
                                ],
                            ],
                        ],
                        'price_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Number::NAME,
                                        'label' => __('Quantity'),
                                        'dataScope' => 'price_qty',
                                    ],
                                ],
                            ],
                        ],
                        'price' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Price::NAME,
                                        'label' => __('Price'),
                                        'enableLabel' => true,
                                        'dataScope' => 'price',
                                        'addbefore' => $this->locator->getStore()
                                                                     ->getBaseCurrency()
                                                                     ->getCurrencySymbol(),
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get stores list
     *
     * @return array
     */
    protected function getStoreViews()
    {
        $stores = [];
        $product = $this->locator->getProduct();

        if ($this->isScopeStore() && $product->getStoreId()) {
            $store = $this->getStore();

            $stores[] = [
                'label' => $store->getName() . '[' . $store->getBaseCurrencyCode() . ']',
                'value' => $store->getId(),
            ];
        } elseif ($this->isScopeStore()) {
            $storesList = $this->storeManager->getStores();
            $productStoreIds = $product->getStoreIds();
            foreach ($storesList as $store) {
                /** @var \Magento\Store\Model\Website $website */
                if (!in_array($store->getId(), $productStoreIds)) {
                    continue;
                }
                $stores[] = [
                    'label' => $store->getName() . '[' . $store->getBaseCurrencyCode() . ']',
                    'value' => $store->getId(),
                ];
            }
        }

        return $stores;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    private function getCustomerGroups()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroupSource = \Magento\Framework\App\ObjectManager::getInstance()->get(GroupSourceInterface::class);
        return $customerGroupSource->toOptionArray();
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    private function getDefaultCustomerGroup()
    {
        return $this->groupManagement->getAllCustomersGroup()->getId();
    }

    /**
     * Retrieve store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    private function getStore()
    {
        return $this->locator->getStore();
    }

    /**
     * @return bool
     */
    protected function isScopeStore()
    {
        return $this->locator->getProduct()
            ->getResource()
            ->getAttribute(self::CODE_TIER_PRICE_FOR_STORE)
            ->isScopeStore();
    }
}
