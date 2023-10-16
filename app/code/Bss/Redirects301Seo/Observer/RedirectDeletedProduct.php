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
 * @package    Bss_Redirects301Seo
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Redirects301Seo\Observer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RedirectDeletedProduct
 *
 * @package Bss\Redirects301Seo\Observer
 */
class RedirectDeletedProduct implements ObserverInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Bss\Redirects301Seo\Helper\UrlIdentifier
     */
    protected $urlIdentifier;

    /**
     * @var \Bss\Redirects301Seo\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * RedirectDeletedProduct constructor.
     * @param \Bss\Redirects301Seo\Helper\UrlIdentifier $urlIdentifier
     * @param \Bss\Redirects301Seo\Helper\Data $dataHelper
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param Context $context
     */
    public function __construct(
        \Bss\Redirects301Seo\Helper\UrlIdentifier $urlIdentifier,
        \Bss\Redirects301Seo\Helper\Data $dataHelper,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Cms\Model\PageFactory $pageFactory,
        Context $context
    ) {
        $this->pageFactory = $pageFactory;
        $this->urlInterface = $context->getUrl();
        $this->dataHelper =  $dataHelper;
        $this->url = $context->getUrl();
        $this->urlIdentifier = $urlIdentifier;
        $this->response = $response;
        $this->actionFlag = $context->getActionFlag();
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->urlIdentifier->getStoreManager()->getStore()->getId();
        $getEnable = $this->dataHelper->getRedirectsEnable($storeId);
        if ($getEnable) {
            //Get Redirect Time to remove all Data over time in Db
            $redirectDay = $this->dataHelper->getRedirectsDay($storeId);

            //Get Redirects Type
            $redirectType = $this->dataHelper->getRedirectsUrl($storeId);
            $baseUrl = $this->getBaseUrl();
            $request = $observer->getData('request');
            $productSuffixUrl = $this->dataHelper->getProductSuffixUrl($storeId);

            //Check URL
            $redirectUrl = $this->urlIdentifier->readUrl(
                $request->getOriginalPathInfo(),
                $productSuffixUrl,
                $redirectDay
            );

            if ($redirectUrl['status'] == true) {
                $categories = $redirectUrl['categories'];
                if ($redirectType === 'category') {
                    $redirectUrlFinal = $this->getFinalUrlByCategory($categories);
                } elseif ($redirectType === 'home') {
                    $redirectUrlFinal = $baseUrl;
                } elseif ($redirectType === 'category_priority') {
                    $isPriority = true;
                    $redirectUrlFinal = $this->getFinalUrlByCategory($categories, $isPriority);
                } else {
                    $redirectUrlFinal = $this->getFinalUrlByCms($redirectType);
                }

                $this->redirectUrl($redirectType, $redirectUrlFinal);
            }
        }
    }

    /**
     * @param string $categoryIds
     * @param bool $isPriority
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFinalUrlByCategory($categoryIds, $isPriority = false)
    {
        $baseUrl = $this->getBaseUrl();

        $categoryIdsObject = explode('-', $categoryIds);
        $redirectUrlFinal = null;
        rsort($categoryIdsObject);
        foreach ($categoryIdsObject as $id) {
            $categoryUrl = $this->urlIdentifier->getCategoryUrl($id, $isPriority);
            if ($categoryUrl) {
                $redirectUrlFinal = $categoryUrl;
                break;
            } else {
                continue;
            }
        }
        if ($redirectUrlFinal === null) {
            $redirectUrlFinal = $baseUrl;
        }

        return $redirectUrlFinal;
    }

    /**
     * @param string $redirectType
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFinalUrlByCms($redirectType)
    {
        $redirectTypeObject = explode('_', $redirectType);
        if (isset($redirectTypeObject[1]) && is_numeric($redirectTypeObject[1])) {
            $cmsPageId = $redirectTypeObject[1];
            $cmsPageObject = $this->pageFactory->create()->load($cmsPageId);

            $statusStoreView = $this->checkStatusCmsStoreView($cmsPageObject);
            if ($cmsPageObject && $cmsPageObject->getIsActive() && $statusStoreView) {
                //Current Url
                $cmsPageUrl = $this->getBaseUrl() . '/' . $cmsPageObject->getData('identifier');
                return $cmsPageUrl;
            } else {
                return $this->getBaseUrl();
            }
        } else {
            return $this->getBaseUrl();
        }
    }

    /**
     * @param object $cmsPageObject
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkStatusCmsStoreView($cmsPageObject)
    {
        $cmsStoreView = $cmsPageObject->getStoreId();

        $storeId = $this->urlIdentifier->getStoreManager()->getStore()->getId();
        $statusStoreView = false;
        if (isset($cmsStoreView[0]) && (int)$cmsStoreView[0] === 0) {
            $statusStoreView = true;
        }
        if (!empty($cmsStoreView)) {
            foreach ($cmsStoreView as $storeViewId) {
                if ((int)$storeViewId === (int)$storeId) {
                    $statusStoreView = true;
                }
            }
        }

        return $statusStoreView;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        $baseUrl = $this->urlIdentifier->getStoreManager()->getStore()->getBaseUrl();
        $baseUrl = rtrim($baseUrl, '/');
        return $baseUrl;
    }

    /**
     * Get redirect url
     *
     * @param string $redirectType
     * @param string $directUrl
     * @return $this
     */
    protected function redirectUrl($redirectType, $directUrl)
    {
        $redirectUrlHomePage = $this->url->getUrl('');
        if ($redirectType == 'index') {
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            return $this->response->setRedirect($redirectUrlHomePage);
        } else {
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            return $this->response->setRedirect($directUrl);
        }
    }
}
