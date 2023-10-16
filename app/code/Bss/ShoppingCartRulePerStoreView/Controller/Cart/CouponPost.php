<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ShoppingCartRulePerStoreView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ShoppingCartRulePerStoreView\Controller\Cart;

class CouponPost extends \Magento\Checkout\Controller\Cart
{
    /**
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

     /**
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

     /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $loggerInterface;

     /**
     *
     * @var \Bss\ShoppingCartRulePerStoreView\Model\ResourceModel\Rule\Collection
     */
    protected $collection;

     /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Escaper $escaper
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Bss\ShoppingCartRulePerStoreView\Model\ResourceModel\Rule\Collection $collection
     * @param \Magento\Customer\Model\Session $customerSession
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Escaper $escaper,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bss\ShoppingCartRulePerStoreView\Model\ResourceModel\Rule\Collection $collection,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
        $this->resource = $resource;
        $this->escaper = $escaper;
        $this->loggerInterface = $loggerInterface;
        $this->collection = $collection;
        $this->customerSession = $customerSession;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $checkStoreView = false; 
        $customerGroupId = $this->getCustomerGroupId();
        $rules = $this->collection->addWebsiteGroupDateFilter($websiteId, $customerGroupId);
        
        $codeLength = strlen($couponCode);
        $oldCouponCodeLength = strlen($oldCouponCode);
        if (!$codeLength && !$oldCouponCodeLength) {
            return $this->_goBack();
        }
        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            $this->checkItemsCount($itemsCount, $cartQuote, $isCodeLengthValid, $couponCode);

            if ($codeLength) {
                $escaper = $this->escaper;
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                $ruleId = $coupon->getRuleId();
                foreach ($rules as $rule) {
                    if ($ruleId == $rule->getRuleId() && $couponCode == $rule->getCode()) {
                        $checkStoreView = true;
                    }
                }
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId() && $checkStoreView) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $this->messageManager->addSuccess(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addError(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode() && $checkStoreView) {
                        $this->messageManager->addSuccess(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addError(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                }
            } else {
                $this->messageManager->addSuccess(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the coupon code.'));
            $this->loggerInterface->critical($e);
        }

        return $this->_goBack();
    }

    /**
     * @param $itemsCount
     * @param $cartQuote
     * @param $isCodeLengthValid
     * @param $couponCode
     */
    protected function checkItemsCount($itemsCount, $cartQuote, $isCodeLengthValid, $couponCode)
    {
        if ($itemsCount) {
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
            $this->quoteRepository->save($cartQuote);
        }
    }

    /**
     * @return int
     */
    protected function getCustomerGroupId()
    {
        $customerGroup = 0;
        if ($this->customerSession->isLoggedIn()) {
            $customerGroup = $this->customerSession->getCustomer()->getGroupId();
        }
        return $customerGroup;
    }
}
