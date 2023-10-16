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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingTierPrice\Model\Product\Attribute\Backend\Tierprice;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;

class UpdateHandler extends \Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPoll;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    private $tierPriceResource;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $tierPriceResource
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $attributeRepository,
        GroupManagementInterface $groupManagement,
        MetadataPool $metadataPool,
        Tierprice $tierPriceResource
    ) {
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->groupManagement = $groupManagement;
        $this->metadataPoll = $metadataPool;
        $this->tierPriceResource = $tierPriceResource;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $attribute = $this->attributeRepository->get('tier_price');
        $priceRows = $entity->getData($attribute->getName());
        if (null !== $priceRows) {
            if (!is_array($priceRows)) {
                throw new \Magento\Framework\Exception\InputException(
                    __('Tier prices data should be array, but actually other type is received')
                );
            }
            $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
            $productId = $entity->getData($identifierField);

            // prepare original data to compare
            $old = $entity->getOrigData($attribute->getName());

            if (!is_array($old)) {
                throw new \Magento\Framework\Exception\InputException(
                    __('Tier prices data should be array, but actually other type is received')
                );
            }

            // prepare data for save
            $new = $this->prepareNewDataForSave($priceRows, 1);

            $delete = array_diff_key($old, $new);
            $insert = array_diff_key($new, $old);
            $update = array_intersect_key($new, $old);

            $isAttributeChanged = $this->deleteValues($productId, $delete);
            $isAttributeChanged |= $this->insertValues($productId, $insert);
            $isAttributeChanged |= $this->updateValues($update, $old);

            if ($isAttributeChanged) {
                $valueChangedKey = $attribute->getName() . '_changed';
                $entity->setData($valueChangedKey, 1);
            }
        }

        return $entity;
    }

    /**
     * Check whether price has percentage value.
     *
     * @param array $priceRow
     * @return integer|null
     */
    private function getPercentage(array $priceRow)
    {
        return isset($priceRow['percentage_value']) && is_numeric($priceRow['percentage_value'])
            ? (int)$priceRow['percentage_value']
            : null;
    }

    /**
     * Update existing tier prices for processed product
     *
     * @param array $valuesToUpdate
     * @param array $oldValues
     * @return boolean
     */
    private function updateValues(array $valuesToUpdate, array $oldValues): bool
    {
        $isChanged = false;
        foreach ($valuesToUpdate as $key => $value) {
            if ((!empty($value['value']) && (float)$oldValues[$key]['price'] !== (float)$value['value'])
                || $this->getPercentage($oldValues[$key]) !== $this->getPercentage($value)
            ) {
                $price = new \Magento\Framework\DataObject(
                    [
                        'value_id' => $oldValues[$key]['price_id'],
                        'value' => $value['value'],
                        'percentage_value' => $this->getPercentage($value)
                    ]
                );
                $this->tierPriceResource->savePriceData($price);
                $isChanged = true;
            }
        }

        return $isChanged;
    }

    /**
     * Insert new tier prices for processed product
     *
     * @param int $productId
     * @param array $valuesToInsert
     * @return bool
     */
    private function insertValues(int $productId, array $valuesToInsert): bool
    {
        $isChanged = false;
        $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
        foreach ($valuesToInsert as $data) {
            $price = new \Magento\Framework\DataObject($data);
            $price->setData(
                $identifierField,
                $productId
            );
            $this->tierPriceResource->savePriceData($price);
            $isChanged = true;
        }

        return $isChanged;
    }

    /**
     * Delete tier price values for processed product
     *
     * @param int $productId
     * @param array $valuesToDelete
     * @return bool
     */
    private function deleteValues(int $productId, array $valuesToDelete): bool
    {
        $isChanged = false;
        foreach ($valuesToDelete as $data) {
            $this->tierPriceResource->deletePriceData($productId, null, $data['price_id']);
            $isChanged = true;
        }

        return $isChanged;
    }

    /**
     * @param array $priceRows
     * @param bool $isGlobal
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareNewDataForSave($priceRows, $isGlobal = true): array
    {
        $new = [];
        $priceRows = array_filter($priceRows);
        foreach ($priceRows as $data) {
            if (empty($data['delete'])
                && (!empty($data['price_qty'])
                    || isset($data['cust_group'])
                    || $isGlobal)
            ) {
                $key = $this->getPriceKey($data);
                $new[$key] = $this->prepareTierPrice($data);
            }
        }

        return $new;
    }

    /**
     * Get generated price key based on price data
     *
     * @param array $priceData
     * @return string
     */
    private function getPriceKey(array $priceData): string
    {
        $key = implode(
            '-',
            array_merge([$priceData['website_id'], $priceData['cust_group']], [(int)$priceData['price_qty']])
        );

        return $key;
    }

    /**
     * Prepare tier price data by provided price row data
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareTierPrice(array $data): array
    {
        $useForAllGroups = (int)$data['cust_group'] === $this->groupManagement->getAllCustomersGroup()->getId();
        $customerGroupId = $useForAllGroups ? 0 : $data['cust_group'];
        $tierPrice = array_merge(
            $this->getAdditionalFields($data),
            [
                'website_id' => $data['website_id'],
                'all_groups' => (int)$useForAllGroups,
                'customer_group_id' => $customerGroupId,
                'value' => $data['price'] ?? null,
                'qty' => (int)$data['price_qty']
            ]
        );

        return $tierPrice;
    }

    /**
     * Get additional tier price fields
     *
     * @param array $objectArray
     * @return array
     */
    private function getAdditionalFields(array $objectArray): array
    {
        $percentageValue = $this->getPercentage($objectArray);
        return [
            'value' => $percentageValue ? null : $objectArray['price'],
            'percentage_value' => $percentageValue ?: null,
        ];
    }
}
