<?php
namespace Tatva\Catalog\Block\Product;

use Tatva\Subscription\Model\Subscription;
use \Magento\Checkout\Helper\Cart as CartHelper;

class PricingProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Tatva\Subscription\Model\Subscription
     */
    protected $subscription;
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;
    /**
     * @var Product
     */
    private $amastyProductModel;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
    protected $_storeManager;
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Subscription $subscription,
        CartHelper $cartHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\RecurringPayments\Model\Product $amastyProductModel,
        \Magento\Customer\Model\Session $session
    ){
        $this->subscription = $subscription;
        $this->cartHelper = $cartHelper;
        $this->urlHelper = $urlHelper;
        $this->_storeManager = $storeManager;
        $this->amastyProductModel = $amastyProductModel;
        $this->_customerSession = $session;
        parent::__construct($context);
    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->cartHelper->getAddUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /*New Pricing Page*/    
    public function getIndividualSubscriptionProducts()
    {
        return $this->subscription->getIndividualSubscriptionProductsCollection();
    }

    public function getEducationSubscriptionProducts()
    {
        return $this->subscription->getEducationSubscriptionProductsCollection();
    }

    public function getBusinessSubscriptionProducts()
    {
        return $this->subscription->getBusinessSubscriptionProductsCollection();
    }

    public function getStripeSubscriptionPlanId($product)
    {
        $stripeSubscriptionPlanId = null;
        $plans = $this->amastyProductModel->getActiveSubscriptionPlans($product);
        foreach ($plans as $plan) {
            $stripeSubscriptionPlanId = $plan->getPlanId();
        }
        return $stripeSubscriptionPlanId;
    }
    
    public function getStoreBaseUrl(){
        return $this->_storeManager->getStore(1)->getBaseUrl();
    }
    /**/
    public function getCustomerSession(){
        return $this->_customerSession;
    }
}