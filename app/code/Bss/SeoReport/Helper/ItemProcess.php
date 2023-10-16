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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Helper;

/**
 * Class StringProcess
 * @package Bss\SeoReport\Helper
 */
class ItemProcess
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productLoader;
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * ItemProcess constructor.
     * @param \Magento\Catalog\Model\ProductRepository $productLoader
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productLoader,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->pageFactory = $pageFactory;
        $this->productLoader = $productLoader;
    }

    /**
     * @param array $item
     * @return mixed
     */
    public function processHeadings($item)
    {
        if ($item['headings'] !== null && $item['headings'] !== '') {
            try {
                $dataJson = $this->jsonHelper->unserialize($item['headings']);
                if (isset($dataJson['h1']) && isset($dataJson['h2']) && isset($dataJson['h3']) && isset($dataJson['h4']) && isset($dataJson['h5'])) {
                    $textData = 'H1 [' . $dataJson['h1'] . '] - H2 [' . $dataJson['h2'] . '] - H3 [' . $dataJson['h3'] . '] - H4 [' . $dataJson['h4'] . '] - H5 [' . $dataJson['h5'] . ']';
                    $item['headings'] = $textData;
                } else {
                    $item['headings'] = '';
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
                $item['headings'] = '';
            }
        }
        return $item;
    }

    /**
     * @param array $item
     * @return mixed
     */
    public function processEntityTag($item)
    {
        if ($item['canonical_tag'] !== null && $item['canonical_tag'] !== '') {
            $item['canonical_tag'] = $this->getEntityTagString((int)$item['canonical_tag']);
        }
        if ($item['open_graph'] !== null && $item['open_graph'] !== '') {
            $item['open_graph'] = $this->getEntityTagString((int)$item['open_graph']);
        }
        if ($item['twitter_card'] !== null && $item['twitter_card'] !== '') {
            $item['twitter_card'] = $this->getEntityTagString((int)$item['twitter_card']);
        }
        return $item;
    }

    /**
     * @param bool $dataCheck
     * @return \Magento\Framework\Phrase
     */
    public function getEntityTagString($dataCheck)
    {
        if ($dataCheck) {
            return __("Yes");
        } else {
            return __("No");
        }
    }

    /**
     * @param array $item
     * @return mixed
     */
    public function processEntityImages($item)
    {
        if ($item['images'] !== null && $item['images'] !== '') {
            if ((int)$item['images'] === 0) {
                $item['images'] = __('All images with ALT');
            } elseif ((int)$item['images'] === 1) {
                $item['images'] = (int)$item['images'] . __(' image without ALT');
            } else {
                $item['images'] = (int)$item['images'] . __(' images without ALT');
            }
        }
        return $item;
    }

    /**
     * @param array $item
     * @param string $entityType
     * @param string $entityId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processEntityMeta($item, $entityType, $entityId)
    {
        if ($entityType === 'product') {
            $productObject = $this->getLoadProduct($entityId);
            $metaDescription = $productObject->getData('meta_description');
            $metaTitle = $productObject->getData('meta_title');
            $metaKeywords = $productObject->getData('meta_keyword');
            $mainKeyword = $productObject->getData('main_keyword');
            $item['meta_title'] = $metaTitle;
            $item['meta_keywords'] = $metaKeywords;
            $item['meta_description'] = $metaDescription;
            $item['main_keyword'] = $mainKeyword;
            $item['status'] = $productObject->getStatus();
        }

        if ($entityType === 'category') {
            $storeId = $item['store_id'];
            $categoryObject = $this->getCategoryById($entityId, $storeId);
            $metaDescription = $categoryObject->getData('meta_description');
            $metaTitle = $categoryObject->getData('meta_title');
            $metaKeywords = $categoryObject->getData('meta_keywords');
            $mainKeyword = $categoryObject->getData('main_keyword');
            $item['meta_title'] = $metaTitle;
            $item['meta_keywords'] = $metaKeywords;
            $item['meta_description'] = $metaDescription;
            $item['main_keyword'] = $mainKeyword;
            $item['status'] = $categoryObject->getIsActive();
        }

        if ($entityType === 'cms-page') {
            $cmsObject = $this->loadCMSPage($entityId);
            $metaDescription = $cmsObject->getData('meta_description');
            $metaTitle = $cmsObject->getData('meta_title');
            $metaKeywords = $cmsObject->getData('meta_keywords');
            $mainKeyword = $cmsObject->getData('main_keyword');
            $item['meta_title'] = $metaTitle;
            $item['meta_keywords'] = $metaKeywords;
            $item['meta_description'] = $metaDescription;
            $item['main_keyword'] = $mainKeyword;
            $item['status'] = $cmsObject->getIsActive();
        }
        return $item;
    }

    /**
     * @param string $id
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLoadProduct($id)
    {
        return $this->productLoader->getById($id);
    }

    /**
     * @param string $categoryId
     * @param string $storeId
     * @return \Magento\Catalog\Api\Data\CategoryInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryById($categoryId, $storeId)
    {
        $category = $this->categoryRepository->get($categoryId, $storeId);
        return $category;
    }

    /**
     * @param string $pageId
     * @return \Magento\Cms\Model\Page
     */
    public function loadCMSPage($pageId)
    {
        $page = $this->pageFactory->create()->load($pageId);
        return $page;
    }
}
