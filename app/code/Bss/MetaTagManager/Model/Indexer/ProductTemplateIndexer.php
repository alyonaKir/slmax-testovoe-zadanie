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
namespace Bss\MetaTagManager\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as ViewActionInterface;

/**
 * Class ProductTemplateIndexer
 *
 * @package Bss\MetaTagManager\Model\Indexer
 */
class ProductTemplateIndexer implements ActionInterface, ViewActionInterface
{
    /**
     * @inheritDoc
     *
     * @param int[] $ids
     */
    public function execute($ids)
    {
        //code here!
    }

    /**
     * @inheritDoc
     */
    public function executeFull()
    {
        //code here!
    }

    /**
     * @inheritDoc
     *
     * @param array $ids
     */
    public function executeList(array $ids)
    {
        //code here!
    }

    /**
     * @inheritDoc
     *
     * @param int $id
     */
    public function executeRow($id)
    {
        //code here!
    }
}
