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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

/**
 * Class Data
 * @package Bss\SeoToolbar\Helper
 */
class Data extends AbstractHelper
{
    const SEO_TOOLBAR_ENABLE = 'bss_seo_alt_text/general/enable';
    const SEO_TOOLBAR_ALBUM_PER_PAGE = '20';
    const SEO_ALT_TEXT_PER_PAGE = 100;
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    public $postDataHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CollectionFactory
     */
    private $attributeCollection;
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    private $attributeSet;
    /**
     * @var \Magento\Catalog\Helper\Output
     */
    private $outputHelper;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricing;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param CollectionFactory $attributeCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet
     * @param \Magento\Framework\Pricing\Helper\Data $pricing
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param \Magento\Catalog\Helper\Output $outputHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        CollectionFactory $attributeCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Framework\Pricing\Helper\Data $pricing,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Magento\Catalog\Helper\Output $outputHelper
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->attributeSet = $attributeSet;
        $this->pricing = $pricing;
        $this->outputHelper = $outputHelper;
        $this->attributeCollection = $attributeCollection;
        $this->storeManager = $storeManager;
        $this->postDataHelper = $postDataHelper;
    }

    /**
     * @param array $data
     * @return string
     */
    public function jsonEncode(array $data)
    {
        return $this->jsonHelper->serialize($data);
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function jsonDecode(string $data)
    {
        return $this->jsonHelper->unserialize($data);
    }
    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * @return bool
     */
    public function isEnableModule()
    {
        return $this->scopeConfig->isSetFlag(
            self::SEO_TOOLBAR_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAltTemplate()
    {
        return $this->scopeConfig->getValue(
            'bss_seo_alt_text/general/alt_template',
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return mixed
     */
    public function getFileTemplate()
    {
        return $this->scopeConfig->getValue(
            'bss_seo_alt_text/general/file_template',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get all product variable
     *
     * @return array
     */
    public function getProductVariables()
    {
        $collectionAttribute = $this->getProductAttributeVariables();
        $result = [];
        foreach ($collectionAttribute as $attribute) {
            $result['product_' . $attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        $result['product_attributeset'] = 'Attribute set';
        return $result;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getProductAttributeVariables()
    {
        $collection = $this->attributeCollection->create()->addVisibleFilter();

        $collection->addFieldToFilter(
            [
                'is_searchable', 'attribute_code',
            ],
            [
                '1',
                [
                    ['in', ['special_price', 'country_of_manufacture']],
                ]
            ]
        )
            ->addFieldToFilter('frontend_input', [
                'text', 'textarea', 'select', 'price', 'swatch_visual', 'swatch_text', 'multiselect'
            ])
            ->addFieldToFilter('attribute_code', ['nin'=>['tax_class_id', 'status']]);

        return $collection;
    }
    /**
     * @param int $number
     * @return string
     */
    public function getRandomString($number = 6) : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $number; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handleString($keyComponent, $product, $key, $string)
    {
        $realValue = '';
        if ($keyComponent[0] === 'product') {
            if ($keyComponent[1] == 'attributeset') {
                $attributeSet = $this->attributeSet->get($product->getAttributeSetId());
                $realValue = $attributeSet->getAttributeSetName();
            } else {
                $attributeCode = str_replace($keyComponent[0] . "_", "", $key);
                $attr = $product->getResource()->getAttribute($attributeCode);
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
     * @param string $attr
     * @param object $product
     * @param string $attributeCode
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
            $realValue = $product->getResource()->getAttribute('up_part')->getFrontend()->getValue($product);
        }
        return $realValue;
    }

    /**
     * @param string $imageFile
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl($imageFile)
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        $imageUrl = $mediaUrl . $imageFile;
        return $imageUrl;
    }

    /**
     * @param string $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl($storeId)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
    }
}
