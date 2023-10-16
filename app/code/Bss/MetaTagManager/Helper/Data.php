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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Helper;

use Bss\MetaTagManager\Model\ResourceModel\MetaTemplate\EffectCollection;
use Magento\Store\Model\ScopeInterface as ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 *
 * @package Bss\MetaTagManager\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEO_REPORT_CRAWL_MAX_URL = 100;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Bss\MetaTagManager\Model\ResourceModel\MetaTemplate\EffectCollection
     */
    protected $effectCollection;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricing;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $outputHelper;

    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSet;

    /**
     * Data constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Helper\Context $context
     * @param EffectCollection $effectCollection
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Framework\Pricing\Helper\Data $pricing
     * @param \Magento\Catalog\Helper\Output $outputHelper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Helper\Context $context,
        EffectCollection $effectCollection,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\Pricing\Helper\Data $pricing,
        \Magento\Catalog\Helper\Output $outputHelper,
        StoreManagerInterface $storeManager,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->effectCollection = $effectCollection;
        $this->categoryRepository = $categoryRepository;
        $this->pricing = $pricing;
        $this->outputHelper = $outputHelper;
        $this->attributeSet = $attributeSet;
        parent::__construct($context);
    }

    /**
     * Check if module is enable
     *
     * @return mixed
     */
    public function isActiveBssMetaTag()
    {
        return $this->scopeConfig->getValue('bss_metatagmanager/general/enable', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Add SEO friendly tag
     *
     * @param string $string
     * @param int $numHypen
     * @return string
     */
    public function addHypens($string, $numHypen)
    {
        $numHypen = (int) $numHypen;
        $numHypen *= 2;
        for ($i=1; $i <= $numHypen; $i++) {
            $string = '-' . $string;
        }
        return $string;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Get current category
     *
     * @return mixed
     */
    public function getCurrentCategory()
    {
        $category = $this->registry->registry('current_category');
        return $category;
    }

    /**
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        if ($storeId) {
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
        }
    }

    /**
     * Passing store variable
     *
     * @return array
     */
    public function getStoreVariables()
    {
        return [
            "store_name"    => "Store Name",
            "store_phone"   => "Store Phone Number",
            "store_house"   => "Store Hours of Operation",
            "store_country" => "Store Country",
            "store_region"   => "Store Region/State",
            "store_postcode"   => "Store ZIP/Postal Code",
            "store_city"   => "Store City",
            "store_address"   => "Street Address",
            "store_address2"   => "Street Address Line 2"
        ];
    }

    /**
     * Get category variable
     *
     * @return array
     */
    public function getCategoryVariables()
    {
        return [
            "cate_name" => "Category Name",
            "cate_description" => "Category Description",
        ];
    }

    /**
     * Get all product variable
     *
     * @return array
     */
    public function getProductVariables()
    {
        $collectionAttribute = $this->effectCollection->getProductAttributeVariables();
        $result = [];
        foreach ($collectionAttribute as $attribute) {
            $result['product_' . $attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        $result['product_attributeset'] = 'Attribute set';
        return $result;
    }

    /**
     * @param object $product
     * @param string $string
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convertVar($product, $string)
    {
        $allVar = array_merge(
            $this->getStoreVariables(),
            $this->getCategoryVariables(),
            $this->getProductVariables()
        );
        foreach (array_keys($allVar) as $key) {
            if (strpos($string, '[' . $key . ']') !== false) {
                $keyComponent = explode('_', $key);
                // store variables
                $string = $this->handleString($keyComponent, $product, $key, $string);
            }
        }
        return $string;
    }

    /**
     * @param string $keyComponent
     * @param object $product
     * @param string $key
     * @param string $string
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleString($keyComponent, $product, $key, $string)
    {
        $storeId = $this->registry->registry('bss_store_id');
        $realValue = '';
        if ($keyComponent[0] === 'store') {
            $realValue = $this->getStoreInfo($keyComponent, $product);
        } elseif ($keyComponent[0] === 'cate') {
            $cateId = $this->getCategoryIdFromProduct($product);
            if ($keyComponent[1] === 'name' && (int)$cateId !== 0) {
                $cate = $this->categoryRepository->get($cateId, $product->getStoreId());
                $realValue = $cate->getName();
            }
        } elseif ($keyComponent[0] === 'product') {
            if ($keyComponent[1] == 'attributeset') {
                $attributeSet = $this->attributeSet->get($product->getAttributeSetId());
                $realValue = $attributeSet->getAttributeSetName();
            } else {
                $attributeCode = str_replace($keyComponent[0] . "_", "", $key);
                $attr = $product->getResource()->getAttribute($attributeCode)->setStoreId($storeId);
                if ($attr) {
                    //Code here
                    $realValue = $this->convertVarAdd($attr, $product, $attributeCode);
                }
            }
        }
        $string = str_replace('[' . $key . ']', $realValue, $string);

        return $string;
    }

    /**
     * @param object $product
     * @return int
     */
    public function getCategoryIdFromProduct($product)
    {
        $finalCategoryId = 0;
        $categoryIds = $product->getCategoryIds();
        if ($categoryIds) {
            foreach ($categoryIds as $categoryId) {
                $finalCategoryId = $categoryId;
            }
        }
        return $finalCategoryId;
    }

    /**
     * @param int $number
     * @return string
     */
    public function getRandomString($number = 6) : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $number; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param string $keyComponent
     * @param object $product
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreInfo($keyComponent, $product)
    {
        $realValue = '';
        if ($keyComponent[1] =='name') {
            if ((int)$product->getStoreId()) {
                $realValue = $this->storeManager->getStore($product->getStoreId())->getName();
            } else {
                $realValue = $this->storeManager->getDefaultStoreView()->getName();
            }
        }
        $storeInfo = $keyComponent[1];
        if ((int)$product->getStoreId()) {
            $storeValue = $this->scopeConfig->getValue(
                'general/store_information/' . $storeInfo,
                ScopeInterface::SCOPE_STORE,
                $product->getStoreId()
            );
        } else {
            $storeValue = $this->scopeConfig->getValue(
                'general/store_information/' . $storeInfo,
                ScopeInterface::SCOPE_STORE
            );
        }
        $realValue = ($storeValue) ? $storeValue : $realValue;
        return $realValue;
    }

    /**
     * Convert variable to frontend Label
     *
     * @param object $attr
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     *
     * @return float|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function convertVarAdd($attr, $product, $attributeCode)
    {
        $realValue ='';
        if ($attr->getData('frontend_input') =='text') {
            $realValue = $product->getData($attributeCode);
        } elseif (in_array($attr->getData('frontend_input'), ['swatch_visual', 'swatch_text', 'select'])) {
            if ($attr->usesSource()) {
                $realValue = $product->getAttributeText($attributeCode);
            }
        } elseif ($attr->getData('frontend_input') == 'price') {
            if ($attributeCode == 'price') {
                $priceObj = $product->getPriceInfo()->getPrice('regular_price');
            } else {
                $priceObj = $product->getPriceInfo()->getPrice($attributeCode);
            }

            if (!$priceObj) {
                $price = $priceObj->getValue();
            } else {
                $price = $product->getPriceInfo()->getPrice('final_price')->getValue();
            }
            $realValue = $this->pricing->currency($price, true, false);
        } elseif ($attr->getData('frontend_input') == 'textarea') {
            $realValue = $this->outputHelper->productAttribute(
                $product,
                $product->getData($attributeCode),
                $attributeCode
            );
        } elseif ($attr->getData('frontend_input') == 'multiselect') {
            $realValue = $attr->getFrontend()->getValue($product);
        }
        return $realValue;
    }

    /**
     * @param object $category
     * @param string $string
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convertCategoryVar($category, $string)
    {
        $allVar = array_merge($this->getStoreVariables(), $this->getCategoryVariables());

        foreach (array_keys($allVar) as $key) {
            if (strpos($string, '[' . $key . ']') !== false) {
                $realValue = '';
                $keyComponent = explode('_', $key);
                // store variables
                if ($keyComponent[0] === 'store') {
                    $realValue = $this->getCategoryStoreInfo($keyComponent, $category);
                } elseif ($keyComponent[0] === 'cate') {
                    //$category = $this->getCurrentCategory();
                    if ($keyComponent[1] === 'name') {
                        $realValue = $category->getName();
                    } elseif ($keyComponent[1] === 'description') {
                        $realValue = $category->getDescription();
                    }
                }
                $string = str_replace('[' . $key . ']', $realValue, $string);
            }
        }
        return $string;
    }

    /**
     * @param string $string
     * @return mixed|string|string[]|null
     */
    public function createSlugByString($string)
    {
        if ($string === '' || $string === null) {
            return $string;
        } else {
            $unicode = [
                'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
                'd'=>'đ',
                'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
                'i'=>'í|ì|ỉ|ĩ|ị',
                'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
                'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
                'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
                'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
                'D'=>'Đ',
                'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
                'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
                'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
                'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
                'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
            ];
            foreach ($unicode as $nonUnicode => $uni) {
                $string = preg_replace("/($uni)/i", $nonUnicode, $string);
            }
            //Replaces all spaces with hyphens.
            $string = str_replace(' ', '-', $string);
            // Removes special chars.
            $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);

            // Replaces multiple hyphens with single one.
            $string = preg_replace('/-+/', '-', $string);
        }
        $string = strtolower($string);
        $string = trim($string, '-');
        return $string;
    }

    /**
     * @param string $keyComponent
     * @param object $category
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryStoreInfo($keyComponent, $category)
    {
        $realValue = '';
        if ($keyComponent[1] =='name') {
            if ((int)$category->getStoreId()) {
                $realValue = $this->storeManager->getStore($category->getStoreId())->getName();
            } else {
                $realValue = $this->storeManager->getStore()->getName();
            }
        }
        $storeInfo = $keyComponent[1];
        if ((int)$category->getStoreId()) {
            $storeValue = $this->scopeConfig->getValue(
                'general/store_information/' . $storeInfo,
                ScopeInterface::SCOPE_STORE,
                $category->getStoreId()
            );
        } else {
            $storeValue = $this->scopeConfig->getValue(
                'general/store_information/' . $storeInfo,
                ScopeInterface::SCOPE_STORE
            );
        }

        $realValue = ($storeValue) ? $storeValue : $realValue;
        return $realValue;
    }
    /**
     * Trim string
     *
     * @param string $string
     * @param string $limit
     * @return string
     */
    public function truncateString($string, $limit)
    {
        if ($limit === 0 || $limit === null || $limit === '') {
            return $string;
        }
        if ($string) {
            $resString = '';
            foreach (explode(" ", $string) as $word) {
                $resString .= $word . " ";
                if (strlen($resString) >= $limit) {
                    return $resString;
                }
            }
            return $resString;
        }
        return '';
    }

    /**
     * Get suffix url for product
     *
     * @param string $storeId
     * @return mixed
     */
    public function getProductSuffixUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/product_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get category suffix
     *
     * @param string $storeId
     * @return mixed
     */
    public function getCategorySuffixUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
