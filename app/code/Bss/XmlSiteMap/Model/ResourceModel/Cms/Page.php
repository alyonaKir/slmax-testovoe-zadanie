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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Model\ResourceModel\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page as CmsPage;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Page
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @package Bss\XmlSiteMap\Model\ResourceModel\Cms
 */
class Page extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var MetadataPool
     */
    public $metadataPool;

    /**
     * @var EntityManager
     */
    public $entityManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $cmsCollectionFactory;

    /**
     * @var \Magento\Framework\DataObject
     */
    public $dataObject;

    /**
     * @var \Bss\XmlSiteMap\Helper\Data
     */
    public $sitemapData;

    /**
     * Page constructor.
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param \Bss\XmlSiteMap\Helper\Data $sitemapData
     * @param \Magento\Framework\DataObject $dataObject
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        \Bss\XmlSiteMap\Helper\Data $sitemapData,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $connectionName = null
    ) {
        $this->sitemapData = $sitemapData;
        $this->cmsCollectionFactory = $objectManager;
        $this->dataObject = $dataObject;
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Init resource model (catalog/category)
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cms_page', 'page_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(PageInterface::class)->getEntityConnection();
    }

    /**
     * Get Collection
     *
     * @param int $storeId
     * @param bool $disablePage
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCollection($storeId, $disablePage)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time']
        )->join(
            ['store_table' => $this->getTable('cms_page_store')],
            "main_table.{$linkField} = store_table.$linkField",
            []
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.identifier != ?',
            CmsPage::NOROUTE_PAGE_ID
        )->where(
            'store_table.store_id IN(?)',
            [0, $storeId]
        );

        $pages = [];
        $query = $this->getConnection()->query($select);

        while ($row = $query->fetch()) {
            $page = $this->prepareObject($row);
            $stringItem = (string)$disablePage;
            $stringItem = "," . $stringItem . ",";
            $cmsId = $row['page_id'];
            $cmsId = (string)$cmsId;
            $stringItemValidate = strpos($stringItem, $cmsId);
            if ($stringItemValidate == false) {
                $pages[$page->getId()] = $page;
            }
        }
        return $pages;
    }

    /**
     * Get collection
     *
     * @param int $storeId
     * @param string $additionString
     * @return mixed
     */
    public function getCollectionAddition($storeId, $additionString)
    {
        $helper = $this->sitemapData;
        $devide = $helper->getDevide($storeId);
        $additionArray = explode("\n", $additionString);
        foreach ($additionArray as $key => $value) {
            if ($key == 0 && $devide != 'none') {
                $row['check'] = 1;
            } else {
                $row ['check'] = 0;
            }
            $row['url'] = $value;
            $object = $this->cmsCollectionFactory->create(CmsPage::class);
            $page = $object->setData($row);
            $pages[$key] = $page;
        }
        return $pages;
    }

    /**
     * Prepare object data
     *
     * @param array $data
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareObject(array $data)
    {
        $object = $this->cmsCollectionFactory->create(CmsPage::class);
        $object->setId($data[$this->getIdFieldName()]);
        $object->setUrl($data['url']);
        $object->setUpdatedAt($data['updated_at']);

        return $object;
    }

    /**
     * Load
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);

        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }

        $isId = true;
        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $value = count($result) ? $result[0] : $value;
            $isId = count($result);
        }

        if ($isId) {
            $this->entityManager->load($object, $value);
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->beginTransaction();

        try {
            if (!$this->isModified($object)) {
                $this->processNotModifiedSave($object);
                $this->commit();
                $object->setHasDataChanges(false);
                return $this;
            }
            $object->validateBeforeSave();
            $object->beforeSave();
            if ($object->isSaveAllowed()) {
                $this->_serializeFields($object);
                $this->_beforeSave($object);
                $this->_checkUnique($object);
                $this->objectRelationProcessor->validateDataIntegrity($this->getMainTable(), $object->getData());
                $this->entityManager->save($object);
                $this->unserializeFields($object);
                $this->processAfterSaves($object);
            }
            $this->addCommitCallback([$object, 'afterCommitCallback'])->commit();
            $object->setHasDataChanges(false);
        } catch (\Exception $e) {
            $this->rollBack();
            $object->setHasDataChanges(true);
            throw $e;
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Exception
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
