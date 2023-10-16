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
namespace Bss\XmlSiteMap\Model\ResourceModel\Homepage;

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
 * @package Bss\XmlSiteMap\Model\ResourceModel\Homepage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    public $homepageCollectionFactory;

    /**
     * @var \Magento\Framework\DataObject
     */
    public $dataObject;
    /**
     * @var \Bss\XmlSiteMap\Helper\Data
     */
    private $helperData;

    /**
     * Page constructor.
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param \Magento\Framework\DataObject $dataObject
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Bss\XmlSiteMap\Helper\Data $sitemapData
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bss\XmlSiteMap\Helper\Data $sitemapData,
        $connectionName = null
    ) {
        $this->helperData = $sitemapData;
        $this->homepageCollectionFactory = $objectManager;
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
        $this->_init('cms_block', 'block_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(PageInterface::class)->getEntityConnection();
    }

    /**
     * @param string $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCollection($storeId)
    {
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time']
        );

        $pages = [];
        $query = $this->getConnection()->query($select);

        while ($row = $query->fetch()) {
            if ($row['url'] == 'home-page-block') {
                $row['url'] = null;
                $page = $this->prepareObject($row, $storeId);
                $pages[$page->getId()] = $page;
            }
        }
        return $pages;
    }

    /**
     * @param array $data
     * @param string $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareObject(array $data, $storeId)
    {
        $helper = $this->helperData;
        $object = $this->homepageCollectionFactory->create(CmsPage::class);
        $object->setId($data[$this->getIdFieldName()]);
        $object->setUrl($data['url']);

        $disableModify = $helper->isHomepageModify($storeId);
        if ($disableModify) {
            $object->setUpdatedAt($data['updated_at']);
        }

        return $object;
    }

    /**
     * Load Object
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
