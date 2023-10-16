<?php

namespace Magenmagic\ResourceHints\Observer\Framework\View\Layout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\LayoutInterface;
class Builder implements ObserverInterface
{
    /** @var PageConfig $pageConfig */
    private $pageConfig;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    private $response;

    /**
     * Builder constructor.
     *
     * @param PageConfig $pageConfig
     * @param LayoutInterface $layout
     */
    public function __construct(
        PageConfig      $pageConfig,
        LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->pageConfig = $pageConfig;
        $this->layout = $layout;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $resourceHints = [
            'google' => [
                'resource' => 'https://www.google-analytics.com',
                'type' => 'preconnect',
            ]
        ];

        foreach ($resourceHints as $resource) {
            $this->pageConfig->addRemotePageAsset(
                $resource['resource'],
                'link_rel',
                [
                    'attributes' => ['rel' => $resource['type']]
                ]
            );
        }
        // Check if you are on the index page
        if ($this->isIndexPage()) {
            $this->preloadImagesSlider();
        }
        return $this;
    }

    /**
     * Check if the current page is the index page.
     *
     * @return bool
     */
    private function isIndexPage()
    {
        // Replace 'cms_index_index' with the actual action name of the index page
        return $this->request->getFullActionName() == 'cms_index_index';
    }


    private function preloadImagesSlider()
    {
        // Get the page content
        $pageContent = $this->layout->getOutput();

        // Use a regular expression to find all image tags
        $pattern = '/<img[^>]+class="sp-image"[^>]+src="([^"]+)"[^>]*>/i';
        preg_match_all($pattern, $pageContent, $matches);

        // Loop through the matched image URLs and add preload attributes
        if (!empty($matches[1])) {
            foreach ($matches[1] as $imageUrl) {
                $this->pageConfig->addRemotePageAsset(
                    $imageUrl,
                    'link_rel',
                    [
                        'attributes' => ['rel' => 'preload', 'as' => 'image'],
                    ]
                );
                break;
            }
        }
    }
}