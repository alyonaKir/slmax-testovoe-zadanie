<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Helper;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\View\LayoutFactory;

/**
 * Class ModelLabel
 * @package Bss\ProductLabel\Helper
 */
class SaveObject
{
    /**
     * @var UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * SaveObject constructor.
     * @param UploaderFactory $fileUploaderFactory
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageFactory
     * @param File $file
     * @param DateTime $date
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        UploaderFactory $fileUploaderFactory,
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        File $file,
        DateTime $date,
        LayoutFactory $layoutFactory
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->file = $file;
        $this->date = $date;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @return UploaderFactory
     */
    public function getFileUploaderFactory()
    {
        return $this->fileUploaderFactory;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return AdapterFactory
     */
    public function getImageFactory()
    {
        return $this->imageFactory;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return LayoutFactory
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }
}
