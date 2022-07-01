<?php
namespace Tatva\Deleteaccount\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_customerSession;
    protected $_deleteaccount;
    protected $_messageManager;
    protected $_url;
    protected $_subscription;
    protected $helper;

    public function __construct
    (
        \Tatva\Deleteaccount\Model\Deleteaccount $deleteaccount,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Tatva\Subscription\Model\Subscription $subscription,
        \Tatva\Deleteaccount\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Action\Context $context
    )
    {
        parent::__construct($context);
        $this->_url = $url;
        $this->_deleteaccount = $deleteaccount;
        $this->_subscription = $subscription;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->curl = $curl;
        $this->_customerSession = $customerSession;
        $this->helper=$helper;
    }

    public function execute()
    {

        if($this->getRequest()->getMethod() == 'POST')
        {
            if($this->_customerSession->isLoggedIn()) 
            {
                $params = $this->getRequest()->getParams();                
                $feedback = $this->helper->matchFeedback($params);
                        
                $this->_deleteaccount->deleteAccounttBefore($feedback);
                $this->stoprecurring();
            }
            else
            {
                $this->_redirect("customer/account/login");
            }
        }
        else
        {
            $this->_redirect("customer/account");
        }
    }

    protected function stoprecurring()
    {
        $last_sub_detail = $this->_subscription->getLastPaidSubscriptionhistory();
        
        if($last_sub_detail == "No Subscription" || $last_sub_detail == "Unsubscribed" || $last_sub_detail->getParentCustomerId() != "")
        {
            $params = array('subscription' => 0);            
            $this->_redirect($this->_url->getUrl('deleteaccount/index/finalizedelete/',$params));
        }
        else if($last_sub_detail != null && is_object($last_sub_detail))
        {
            
            //$current_loggedIn_customer = $this->_customerSession->getCustomer();
            //$customer_id =  $current_loggedIn_customer->getId();
            //$customer_email = $current_loggedIn_customer->getEmail();
            $subscription_history_id = $last_sub_detail->getSubscriptionHistoryId();
            $parms = array('flag' => 'deleteacc','subscription_id' => $subscription_history_id);
            
            //$baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            //$uri = $baseUrl.'paypalrec/unsubscribe/?unsub_crondata=1&flag=deleteacc&subscription_id='.$subscription_history_id.'&customer_id='.$customer_id.'&customer_email='.$customer_email;
            
            //$result = $this->curl->get($uri);          

            //$this->_redirect("customer/account");
            
            $this->_redirect($this->_url->getUrl('paypalrec/unsubscribe/',$parms));
        }
        else
        {
            
            $this->_messageManager->addError('We are unable to delete your account right now , please try later.');
            $this->_redirect("customer/account");
        }
    }    
    
}