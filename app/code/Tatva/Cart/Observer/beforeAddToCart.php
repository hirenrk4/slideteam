<?php
namespace Tatva\Cart\Observer;
use Magento\Framework\Event\ObserverInterface;
use Tatva\Subscription\Model\Subscription;
use Tatva\Ebook\Helper\Ebook;

class beforeAddToCart implements ObserverInterface
{
	/**
     * @var \Magento\Quote\Model\Quote
     */
	protected $_quote;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    protected $_responseFactory;
    protected $_url;
    protected $_cart;
    protected $_cartHelper;
    protected $_subscriptionProductsIds;
    protected $_subscription;
    protected $_customerSession;
  
	/**
     * @var \Magento\Framework\Data\Form\FormKey
     */
	protected $_formKey;


	public function __construct
	(
		Ebook $ebook,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Checkout\Helper\Cart $cartHelper,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\ResponseFactory $responseFactory,
		\Magento\Framework\UrlInterface $url,
		Subscription $subscription,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Customer\Model\Session $customerSession
	) 
	{
		$this->ebook = $ebook;
		$this->_cart = $cart;
		$this->_cartHelper = $cartHelper;
		$this->_responseFactory = $responseFactory;
		$this->_url = $url;
		$this->_quote = $checkoutSession->getQuote();
		$this->_messageManager = $messageManager;
		$this->_subscription = $subscription;
		$this->_customerSession = $customerSession;
		$this->_formKey = $formKey;
		
		// Some times below validation of virtual product is not working so need to fix with other alternatives
		$this->_subscriptionProductsIds = $this->_subscription->getSubscriptionProductsIds();
	}

	public function execute(\Magento\Framework\Event\Observer $observer){
		
		if($this->_cartHelper->getItemsCount() != 0)
		{
			$this->_cart->truncate();	
		}
		
		if($this->_customerSession->isLoggedIn()){
			/**
			 * Restricts products other than virtual to be added to cart
			 */
			$items = $this->_quote->getAllVisibleItems();
			
			// Remove products from cart if any 
			// This will never happen as we have already truncated it
			
			if(count($items) > 0){
				$this->_cart->truncate();
			}
			
			$this->validateSubscriptionPurchase($observer);
			
		}
		else{
			$params = $observer->getRequest()->getParams();
			if(isset($params['product']) && !empty($params['product']))
			{
				$this->sendToLoginWithAddToCartParams($observer);
			}
			else
			{
				$login_url = $this->_url->getUrl('customer/account/login/');
				$observer->getControllerAction()->getResponse()->setRedirect($login_url);
				$this->_responseFactory->create()->setRedirect($login_url)->sendResponse();
				die();	
			}
		}
	}


	protected function truncateCartSendToPricing($observer){
		
		$this->_cart->truncate();
		
        //set false if you not want to add product to cart
		$observer->getRequest()->setParam('product', false);
		
        // Returns url with trailing slash need to remove it
		$pricing_url = $this->_url->getUrl('pricing');

		// Trailing slash removed
		if(substr($pricing_url, -1) == "/"){
			$pricing_url = substr($pricing_url,0,-1);
		}
		$observer->getControllerAction()->getResponse()->setRedirect($pricing_url);
		$this->_responseFactory->create()->setRedirect($pricing_url)->sendResponse();
		die();        		                
	}


	protected function truncateCartSendToSubscriptionListing($observer){
		
		$this->_cart->truncate();
		
        //set false if you not want to add product to cart
		$observer->getRequest()->setParam('product', false);
		
        // Returns url with trailing slash need to remove it
		$subscription_list_url = $this->_url->getUrl('subscription/index/list');

		// Trailing slash removed
		if(substr($subscription_list_url, -1) == "/"){
			$subscription_list_url = substr($subscription_list_url,0,-1);
		}
		$observer->getControllerAction()->getResponse()->setRedirect($subscription_list_url);
		$this->_responseFactory->create()->setRedirect($subscription_list_url)->sendResponse();
		die();        		                
	}

	protected function truncateCartSendToeBook($observer){
		$this->_cart->truncate();
		
        //set false if you not want to add product to cart
		$observer->getRequest()->setParam('product', false);
		
        // Returns url with trailing slash need to remove it
		$subscription_list_url = $this->_url->getUrl('powerpoint-ebooks-for-slide-template-design');

		// Trailing slash removed
		if(substr($subscription_list_url, -1) == "/"){
			$subscription_list_url = substr($subscription_list_url,0,-1);
		}
		$observer->getControllerAction()->getResponse()->setRedirect($subscription_list_url);
		$this->_responseFactory->create()->setRedirect($subscription_list_url)->sendResponse();
		die();        		                
	}

	protected function sendToLoginWithAddToCartParams($observer){
		// Get referer url
		$addToCartUrl = $this->_url->getCurrentUrl();
		$params = $observer->getRequest()->getParams();

		// Create login URL
    	$redirectUrl = $this->_url->getUrl("checkout/cart/add",$params);
		$login_url = $this->_url->getUrl('customer/account/login/', 
			array("referer" => base64_encode($redirectUrl),
				"cart"=>"1"));
		$this->_customerSession->setCheckoutUrl($redirectUrl);
		// Login Page Message to login/signup to purchase subscription
		/*$this->_messageManager->addNotice(__("Please login/signup to purchase the subscription."));*/

       	// Redirect to login URL
		$observer->getControllerAction()->getResponse()->setRedirect($login_url);
		$this->_responseFactory->create()->setRedirect($login_url)->sendResponse();
		die();		
	}


	protected function validateSubscriptionPurchase($observer){
		$this->_cart->truncate();
		$params = $observer->getRequest()->getParams();
		$productId = $params['product'];

		$can_purchase = $this->_subscription->productCanBeDownloaded($productId);
		
		// Need to login
		if($can_purchase == 0){
			$this->sendToLoginWithAddToCartParams($observer);
		}
		elseif($can_purchase == 1){
			$isCustomerSubscribed = $this->ebook->isCustomerSubscribed();
			$alreadysubscribed = 0;
			if($isCustomerSubscribed != null){
				$current_date = strtotime(date('Y-m-d H:i:s'));
			    $from_date = strtotime($isCustomerSubscribed->getFromDate());
			    $to_date = strtotime($isCustomerSubscribed->getToDate());
			    $subscription_status = $isCustomerSubscribed->getStatusSuccess();

			    if ($current_date >= $from_date && $current_date <= $to_date && $subscription_status != "Failed") {
			    	$alreadysubscribed = 1;
			    }
			}
			$groupEbook = $this->ebook->getGroupEbook();	
			$isCustomerPurchasedAll = $this->ebook->isCustomerPurchased($groupEbook->getEntityId());
			$isCustomerPurchased = $this->ebook->isCustomerPurchased($productId);
			$productCollection = $this->ebook->getEbookProductCollection();
			$allProductsArePurchased = $this->ebook->allProductsArePurchased($productCollection);
			$isEbook = $this->_subscription->isProductEbook($productId);

			if(($alreadysubscribed == 1 || $isCustomerPurchasedAll == TRUE || $isCustomerPurchased == TRUE || $allProductsArePurchased == TRUE) && $isEbook == 1){
				$this->_messageManager->addSuccess(__('You can now download eBooks'));
				$this->truncateCartSendToeBook($observer);
			} else { 
				$this->_messageManager->addNotice(__('You have already paid for the Subscription. You can download any Product.'));
				$this->truncateCartSendToSubscriptionListing($observer);
			}
		}
		elseif ($can_purchase == 2) {
			if(in_array($productId, $this->_subscriptionProductsIds)){
				$updated_form_key = $this->_formKey->getFormKey();
				$observer->getRequest()->setParam('form_key',$updated_form_key);
			}
			else{
				//added for ebook download
				$isEbook = $this->_subscription->isProductEbook($productId);
				if($isEbook == 1){
					$updated_form_key = $this->_formKey->getFormKey();					
					$observer->getRequest()->setParam('form_key',$updated_form_key);		
				} else {
					$this->_messageManager->addError(__('Invalid Subscription, Select any plan that suits you or please contact admin.'));
					$this->truncateCartSendToPricing($observer);   
				}
			}
		}
		elseif($can_purchase == 3){
			$this->_messageManager->addNotice(__('Please Unsubscribe from current Subscription Plan to purchase new Subscription Plan or contact admin.'));
			$this->truncateCartSendToSubscriptionListing($observer);
		}
	}

}