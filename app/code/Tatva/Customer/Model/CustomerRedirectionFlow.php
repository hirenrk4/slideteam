<?php

namespace Tatva\Customer\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Tatva\Subscription\Model\Subscription;

class CustomerRedirectionFlow {

    /**
     * [$_subscription]
     * @var [\Tatva\Subscription\Model\Subscription;]
     */
    protected $_subscription;

    /**
     * [$_urlInterface ]
     * @var [\Magento\Framework\UrlInterface]
     */
    protected $_urlInterface;

    /**
     * [$_customerSession ]
     * @var [\Magento\Customer\Model\Session]
     */
    protected $_customerSession;

    protected $_productRepository;

    protected $_coreSession;

    protected $_request;

    public function __construct(
        UrlInterface $urlInterface,
        Subscription $subscription,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customerSession
        ) {
        $this->_urlInterface = $urlInterface;        
        $this->_subscription = $subscription;
        $this->_productRepository = $productRepository;
        $this->_coreSession = $coreSession;
        $this->_request = $request;
        $this->_customerSession = $customerSession;
    }

    public function getRedirectUrlForCustomer()
    {
        $redirectUrl = $this->_urlInterface->getUrl();
        
        if($checkouUrl = $this->_customerSession->getCheckoutUrl()){
            $redirectUrl = $checkouUrl;
            // $this->_customerSession->unsCheckoutUrl();
            return $redirectUrl;
        }

        // Check if customer is redirected by product page then process accordingly
        $social_postData = $this->_coreSession->getSocialPostData();
        $product_id = isset($social_postData['product_id']) ? $social_postData['product_id'] : 0;	
        $params = $this->_request->getParams();
        
        if($product_id > 0)
        {            
            $productCanBeDownloaded = $this->_subscription->productCanBeDownloaded($product_id);
    
            if($productCanBeDownloaded == 2)
            {
                $redirectUrl = $this->_urlInterface->getUrl('pricing');                
            }
            elseif ($productCanBeDownloaded == 3) 
            {
                $redirectUrl = $this->_urlInterface->getUrl('subscription/index/list');
            }
            else{
                $redirectUrl = $this->_productRepository->getById($product_id)->getProductUrl();
                // $this->_coreSession->unsSocialPostData();
            }
        }
        else
        {           
            // Else Check customer type and redirect accordingly        
            $subscription = $this->_subscription->getCustomerType();
            
            if (is_array($subscription)) {
                $customerType = $subscription['customerType'];
            } else {
                $customerType = $subscription;
            }

            if($customerType == $this->_subscription::CUSTOMER_SUBSCRIPTION_STATUS_FREE || $customerType == $this->_subscription::CUSTOMER_SUBSCRIPTION_STATUS_EXPIRED)
            {
                $redirectUrl = $this->_urlInterface->getUrl('pricing');
            }
            elseif($customerType == $this->_subscription::CUSTOMER_SUBSCRIPTION_STATUS_DOWNLOAD_LIMIT_EXAUSTED || $customerType == $this->_subscription::CUSTOMER_SUBSCRIPTION_STATUS_ACTIVE)
            {
                $redirectUrl = $this->_urlInterface->getUrl('subscription/index/list');
            } 
        }        
        return $redirectUrl;
    }
}