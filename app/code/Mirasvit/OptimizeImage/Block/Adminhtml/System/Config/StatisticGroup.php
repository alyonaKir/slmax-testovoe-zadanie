<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-optimize
 * @version   2.0.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\OptimizeImage\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\BlockInterface;
use Mirasvit\OptimizeImage\Service\FileStatisticService;
use Mirasvit\OptimizeImage\Service\FormatService;

class StatisticGroup extends Field
{
    private $fileStatisticService;

    private $formatService;

    public function __construct(
        FileStatisticService $fileStatisticService,
        FormatService $formatService,
        Context $context,
        array $data = []
    ) {
        $this->fileStatisticService = $fileStatisticService;
        $this->formatService        = $formatService;

        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    public function getTotalFilesCount(): int
    {
        return $this->fileStatisticService->getTotalFilesCount();
    }

    public function getProcessedFilesCount(): int
    {
        return $this->fileStatisticService->getProcessedFilesCount();
    }

    public function getWebpFilesCount(): int
    {
        return $this->fileStatisticService->getWebpFilesCount();
    }

    public function getProcessedSize(): string
    {
        return $this->formatService->formatBytes($this->fileStatisticService->getProcessedSize());
    }

    public function getSavedSize(): string
    {
        return $this->formatService->formatBytes($this->fileStatisticService->getSavedSize());
    }

    protected function _prepareLayout(): BlockInterface
    {
        parent::_prepareLayout();

        $this->setTemplate('Mirasvit_OptimizeImage::system/config/statistic.phtml');

        return $this;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $this->addData([
            'html_id' => $element->getHtmlId(),
        ]);

        return $this->_toHtml();
    }
}
