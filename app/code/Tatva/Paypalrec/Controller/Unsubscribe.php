<?php
namespace Tatva\Paypalrec\Controller;

use \Magento\Framework\App\Action\Action;

abstract class Unsubscribe extends Action
{

    /**
     * @var string $payment_method
     */
    private $_paymentMethod;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * @var \Tatva\Subscription\Model\SubscriptionFactory
     */
    protected $_subscriptionSubscriptionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_generic;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_salesOrderFactory;

    /**
     * @var \Tatva\Paypalrec\Model\ResourceModel\Result\CollectionFactory
     */
    protected $_paypalrecResultCollectionFactory;

     /**
     * @var \Tatva\Paypalrec\Model\ResourceModel\PaypalRecurringMapper\CollectionFactory
     */
    protected $_paypalrecMapperResultCollectionFactory;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $_emailTemplateFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_inlineTranslation;

    /**
     * [$_notification description]
     * @var \Tco\Checkout\Model\Ins
     */
    protected $_ins;
    /**
     * [$_checkout description]
     * @var \Tco\Checkout\Model\Checkout
     */
    protected $_checkout;

    protected  $_sendemail;

    protected  $_urlInterface;

    protected $httpClientFactory;
    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;
    /**
     * @var SubscriptionCancelOperation
     */
    private $subscriptionCancelOperation;
    /**
     * @var Session
     */
    private $session;

    /**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Backend\Model\Auth\Session $_backendAuthSession,
        \Tatva\Subscription\Model\Subscription $_subscriptionSubscriptionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $_salesOrderFactory,
        \Tatva\Paypalrec\Model\ResourceModel\Result\CollectionFactory $_paypalrecResultCollectionFactory,
        \Tatva\Paypalrec\Model\ResourceModel\PaypalRecurringMapper\CollectionFactory $_paypalrecMapperResultCollectionFactory,
        \Magento\Email\Model\TemplateFactory $_emailTemplateFactory,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Payment\Helper\Data $_paymentHelper,
        \Magento\Framework\Translate\Inline\StateInterface $_inlineTranslation,
        \Tco\Checkout\Model\Ins $_ins,
        \Tco\Checkout\Model\Checkout $_checkout,
        \Tatva\Paypalrec\Helper\SendEmail $_sendemail,
        \Magento\Framework\UrlInterface $_urlInterface,
        \Magento\Backend\Helper\Data $_helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Amasty\RecurringPayments\Api\Subscription\RepositoryInterface $subscriptionRepository,
        \Amasty\RecurringPayments\Model\Subscription\Operation\SubscriptionCancelOperation $subscriptionCancelOperation,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Customer $customerModel,
        \Tatva\Subscription\Helper\Data $SubscriptionHelper,
        \Tatva\Subscription\Helper\TeamPlans $teamplanModel
    ) {
        $this->_backendAuthSession = $_backendAuthSession;
       $this->_subscription = $_subscriptionSubscriptionFactory;
       $this->_scopeConfig = $_scopeConfig;
       $this->__messageManager = $messageManager;
       $this->_salesOrderFactory = $_salesOrderFactory;
       $this->_paypalrecResultCollectionFactory = $_paypalrecResultCollectionFactory;
       $this->_paypalrecMapperResultCollectionFactory = $_paypalrecMapperResultCollectionFactory;
       $this->_emailTemplateFactory = $_emailTemplateFactory;
       $this->_storeManager = $_storeManager;
       $this->_paymentHelper = $_paymentHelper;
       $this->_inlineTranslation = $_inlineTranslation;
       $this->_ins = $_ins;
       $this->_checkout = $_checkout;
       $this->sendemail = $_sendemail;
       $this->_urlInterface = $_urlInterface;
       $this->_helper = $_helper;
       $this->transportBuilder = $transportBuilder;
       $this->httpClientFactory = $httpClientFactory;
       $this->_logger = $logger;
       $this->date = $date;
       $this->EmarsysHelper = $EmarsysHelper;
       $this->emarsysApiHelper = $emarsysApiHelper;
       $this->zohoCRMHelper = $zohoCRMHelper;
       $this->subscriptionRepository = $subscriptionRepository;
       $this->subscriptionCancelOperation = $subscriptionCancelOperation;
       $this->session = $session;
       $this->_registry = $registry;
       $this->_customer = $customerModel;
       $this->SubscriptionHelper = $SubscriptionHelper;
       $this->_teamplanModel = $teamplanModel;
        parent::__construct(
            $context
        );
    }


    public function Index()
    {
        $flag = $this->getRequest()->getParam('flag');
              $customer_id="";  
        
        if($flag == 'deleteacc'){
            $subscriptionId = $this->getRequest()->getParam('subscription_id');
        }
        $pflag = $this->getRequest()->getParam('pflag');
        $subscriptionId = $this->getRequest()->getParam('subscription_id');
        if($pflag == 'adminunsub'){
         // $subscriptionId = $this->getRequest()->getParam('subscription_id');
           $customer_id = $this->getRequest()->getParam('customer_id');

           $this->_backendAuthSession->setadmincustomer1($customer_id);
        }
                 
        $params = $this->getRequest()->getParams();
         
        if (isset($params['unsub_crondata']) && $params['unsub_crondata'] == 1) {
            $customer_id = $customerId = $this->getRequest()->getParam('customer_id');
            $subscriptionId = $this->getRequest()->getParam('subscription_id');
        }
        else
        {
            //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            //$customersession  = $objectManager->get('\Magento\Customer\Model\Session');
            $customersession  = $this->session;
            $customer_id = $customersession->getCustomer()->getId();
            $customerId = $this->session->getCustomerId();
            
        }
        
        
        $subscription_history_detail = "";
        $subscription_history_detail = $this->_subscription->getLastPaidSubscriptionhistory($customer_id);
        
        //$logger->info($subscription_history_detail->getData());
                        
        if (is_object($subscription_history_detail) && $subscription_history_detail !== "Unsubscribed")
        {
            $subscription_history_id = $subscription_history_detail->getId();
 
            if (isset($subscriptionId) && $subscriptionId != "" && $subscriptionId == $subscription_history_id)
            {
                $two_checkout_message_id = $subscription_history_detail->getTwoCheckoutMessageId();
                $stripe_checkout_message_id = $subscription_history_detail->getStripeCheckoutMessageId();

                if ($two_checkout_message_id != "" && $two_checkout_message_id != "0")
                {
                    
                    $this->payment_method = 'tco';

                    //curl -X POST https://www.2checkout.com/api/sales/stop_lineitem_recurring -u \'username:password' -d 'vendor_id=123456' -d 'lineitem_id=1234567890' \-H 'Accept: application/json'
                    // echo $two_checkout_message_id;die;   
                   $two_checkout_model = $this->_ins->load($two_checkout_message_id);

                    if (is_object($two_checkout_model) && $two_checkout_model->getSaleId() != "")
                    {
                        $unsubscribed = false;
                        $recurring_stopped = false;
                        $headers = array(
                            "Accept: application/json",
                        );
                         
                        
                        $sale_id = $two_checkout_model->getSaleId();

                        $post_fields = array();
                        $post_fields["vendor_id"] = $this->_scopeConfig->getValue("payment/tco_checkout/merchant_id", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $post_fields["lineitem_id"] = $sale_id;

                        $api_username = $this->_scopeConfig->getValue("payment/tco_checkout/api_user", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $api_password = $this->_scopeConfig->getValue("payment/tco_checkout/api_pass", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                        $line_item_ids = $this->getLineItemIds($sale_id);
                        
                        $is_sandbox = $this->_checkout->getConfigData('sandbox');
                        if (is_array($line_item_ids) && count($line_item_ids) > 0)
                        {
                            if(!isset($line_item_ids['error_code']))
                            {
                                foreach ($line_item_ids as $line_item_id)
                                {
                                    $result = $this->stopRecurring($is_sandbox, $api_username, $api_password, $headers, $line_item_id);
 
                                    if(is_object($result)){
                                        if ($result->response_code == "OK")
                                        {
                                            if($flag != 'deleteacc' || $pflag != 'adminunsub'){
                                             //Mage::log($pflag,null,"flag.log");
                                             $this->__messageManager->addSuccess('You have successfully Unsubscribed.');   
                                            }
                                            $date = $this->date->gmtDate();
                                            $checkout_ins_id = $two_checkout_message_id;
                                            $subscription_history_detail->setData("two_checkout_message_id", $two_checkout_message_id);
                                            $subscription_history_detail->setData("status_success", "Unsubscribed");
                                            $subscription_history_detail->setData("update_time", $date);
                                            $subscription_history_detail->save();
                                            $unsubscribed = true;

                                            $this->UnSubscribeChild($subscription_history_detail,"Unsubscribed");
                                        }
                                        
                                    }
                                    //$order = $this->_salesOrderFactory->create()->loadByAttribute("increment_id", $subscription_history_detail->getIncrementId());                                    
                                    
                                    if($flag != 'deleteacc'){
                                        $this->getCustomMailSend($unsubscribed,$subscription_history_detail);
                                        //$this->sendUnsubscriptionRequestMailToAdmin($subscription_history_detail, $order, $unsubscribed);
                                    }
                                    break;
                                }
                            }
                        }
                        else
                        {
                            //$order = $this->_salesOrderFactory->create()->loadByAttribute("increment_id", $subscription_history_detail->getIncrementId());

                            if($flag != 'deleteacc'){

                                $this->getCustomMailSend($unsubscribed,$subscription_history_detail);
                                //$this->sendUnsubscriptionRequestMailToAdmin($subscription_history_detail, $order);
                            }                           
                        }
                    }
                }elseif (!empty($stripe_checkout_message_id)){
                    $this->payment_method = 'stripe';
                    $unsubscribed = false;

                    try {
                        $subscription = $this->subscriptionRepository->getBySubscriptionId($stripe_checkout_message_id);
                    } catch (NoSuchEntityException $e) {
                        $subscription = null;
                    }

                    
                    if ($subscription && $subscription->getCustomerId() == $customerId) {
                        $unsubscribed = $this->subscriptionCancelOperation->execute($subscription);
                    }
                    
                    if($flag != 'deleteacc'){
                        $this->getCustomMailSend($unsubscribed,$subscription_history_detail);
                    }
                    if($pflag != 'adminunsub'){
                        if($unsubscribed){
                            $date = $this->date->gmtDate();
                            $subscription_history_detail->setData("status_success", "Unsubscribed");
                            $subscription_history_detail->setData("update_time", $date);
                            $subscription_history_detail->save();

                            $this->UnSubscribeChild($subscription_history_detail,"Unsubscribed");

                            $this->__messageManager->addSuccess('Customer has been unsubscribed successfully.');
                        }else{

                            $this->getCustomMailSend(0,$subscription_history_detail);

                            $this->__messageManager->addError('Unable to Unsubscribe.');
                        }
                    }
                }else{
                    if ($subscription_history_detail->getPaypalId() != "" && $subscription_history_detail->getPaypalId() != "0"){
                    
                    $this->payment_method = 'paypal';
                    $paypalId='';
                    /*Paypal Auto Unsubscribe Snippet starts*/
                    
                    /** This code is for old paypal subscription. For new order this data will be empty. **/
                    
                    $paypalCollection = $this->_paypalrecResultCollectionFactory->create()->addFieldToSelect(array('test_ipn','paypal_id','transaction_log'))
                            ->addFieldToFilter('increment_id',$subscription_history_detail->getIncrementId());
                    $paypalCollection->getSelect()->group('increment_id');                     

                    if(!empty($paypalCollection)){
                        
                        foreach($paypalCollection as $data)
                        {                            
                            $paypalId = $data->getPaypalId();                            
                            $sandboxMode = $data->getTestIpn();
                            $order_details = $data->getTransactionLog();
                        }
                    }
                    /** This code is for old paypal subscription. For new order this data will be empty. **/
                    
                    /** This code is for new paypal subscription. For new order increment id will be like this 'INV2-ZZQ4-RZKD-XYAA-MQ7L' **/
                    
                    $paypalMappingCollection= $this->_paypalrecMapperResultCollectionFactory->create()->addFieldToSelect('rp_profile_id')->addFieldToFilter('invoice',$subscription_history_detail->getIncrementId());
                    $paypalMappingCollection->setOrder('map_id','DESC');

                    $paypalMappingCollection->getSelect()->limit(1);
                    
                    if(empty($paypalId))
                    {
                        if(!empty($paypalMappingCollection)){
                            
                            foreach($paypalMappingCollection as $mappingData)
                            {
                                $paypalId = $mappingData->getRpProfileId();
                            }
                        }
                         $paypalCollection = $this->_paypalrecResultCollectionFactory->create()->addFieldToSelect(array('test_ipn','transaction_log'))
                            ->addFieldToFilter('paypal_id',$paypalId);
                           $paypalCollection->getSelect()->group('paypal_id');
                           if(!empty($paypalCollection)){
                        
                            foreach($paypalCollection as $data)
                            {
                                $sandboxMode = $data->getTestIpn();
                                $order_details = $data->getTransactionLog();
                            }
                        }
                     }
                     
                     /** This code is for new paypal subscription. For new order increment id will be like this 'INV2-ZZQ4-RZKD-XYAA-MQ7L' **/
                        if(!empty($order_details)){
                                  
                            /*$temp = explode(" [",trim($order_details));
                             
                            foreach($temp as $key=>$value)
                            {
                                $value = str_replace(" ","",$value);
                                $value = str_replace("]","",$value);
                                $temp2 = explode("=>",$value);       
                                $newOrderDetail[$temp2[0]] = isset($temp2[1])?$temp2[1]:"";
                            }*/
                            
                            $newtemp = explode("[",trim($order_details));
                            $i = 0;                   
                            foreach($newtemp as $key => $value)
                            {
                                //print_r($key);
                                $explodeValues = explode("=>",trim($value));
                                $explodeValues[0] = str_replace("]","",trim($explodeValues[0]));
                                    
                                if($i == 0)     
                                {
                                    $i++;
                                    continue;
                                }
                                        
                                $explodeValues[1] = str_replace(")","",trim($explodeValues[1]));                                        
                                $newOrderDetail[$explodeValues[0]] = isset($explodeValues[1])?$explodeValues[1]:"";                                     
                            }   
                            $business = trim($newOrderDetail['receiver_email']);
                            if(empty($business))
                            {
                                $business = trim($newOrderDetail['business']);
                            }
                                 
                             $ch = curl_init();
                                  
                            if($sandboxMode==1)
                            {
                                $api_endpoint = "https://api-3t.sandbox.paypal.com/nvp";
                                $api_username = "nikspatel.tatvasoft-business_api1.gmail.com";
                                $api_password = "X4T3CWA4YJTH5HMV";
                                $api_signature = "AyGRPSOvry9jBguHOnnZLufofQ8aATdHI2p3VrKHnarCRkFdqeB43Wuz";
                                //Additional Parameters Starts
                                curl_setopt($ch, CURLOPT_SSLVERSION,6);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
                                //Additional Parameters Ends
                            }
                            elseif($sandboxMode==0)
                            {
                                $api_endpoint = "https://api-3t.paypal.com/nvp";

                                switch($business)
                                {
                                    case 'sales@slideteam.net':
                                    $api_username = 'sales_api1.slideteam.net';
                                    $api_password = 'JY9SS25W3CZ2DWGN';
                                    $api_signature = 'AQuF.Cedu2rMm98xgCI7ngBkvyv0AwfWqoOx9dBCaDRdpBh66pklFE7y';
                                    break;

                                    case 'accounts@slideteam.net':
                                    $api_username = 'accounts_api1.slideteam.net';
                                    $api_password = 'KGUSZMDR67NXYBCR';
                                    $api_signature = 'ANpb5EVBX4849Qyvr-C.n1EEaYX8Ajifs.JEHpMYz9QD9QiAdndTzHJl';
                                    break;

                                    case 'payments@slideteam.net':
                                    $api_username = 'payments_api1.slideteam.net';
                                    $api_password = '9QFUNN67L63T45EH';
                                    $api_signature = 'A9Q7rtAREG3-GH-w3a9P52W500AnAidSn35GyGyon.AsF3Wk0a2wR.DR';
                                    break;
                                    case 'billing@slideteam.net':
                                    $api_username = 'billing_api1.slideteam.net';
                                    $api_password = '6VTVM8XXVMHWAR3X';
                                    $api_signature = 'AFcWxV21C7fd0v3bYYYRCpSSRl31A7Of0j9OTCPKkAq91gVXbIKKHSvq';
                                    break;
                                }
                            }

                            $api_request = 'USER=' . ( urlencode( $api_username ) )
                                        .  '&PWD=' . ( urlencode( $api_password ) )
                                        .  '&SIGNATURE=' . urlencode( $api_signature )
                                        .  '&VERSION=76.0'
                                        .  '&METHOD=ManageRecurringPaymentsProfileStatus'
                                        .  '&PROFILEID=' . ( $paypalId )
                                        .  '&ACTION=cancel'
                                        .  '&NOTE=' . urlencode('Unsubscribed');

                            curl_setopt( $ch, CURLOPT_URL, $api_endpoint );
                            curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
                            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
                            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
                            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                            curl_setopt( $ch, CURLOPT_POST, 1 );
                            curl_setopt( $ch, CURLOPT_POSTFIELDS, $api_request );

                            $response = curl_exec( $ch );
                            $info=curl_getinfo($ch);

                            // If no response was received from PayPal there is no point parsing the response
                            if(! $response )
                            curl_close( $ch );

                            parse_str( $response, $parsed_response );
   
                            $ackResponse=isset($parsed_response['ACK'])?$parsed_response['ACK']:NULL;
                            $profileIdResponse=isset($parsed_response['PROFILEID'])?$parsed_response['PROFILEID']:NULL;
                            $order = $this->_salesOrderFactory->create()->loadByAttribute("increment_id", $subscription_history_detail->getIncrementId());
                            
                            
                                if(strtoupper($ackResponse) == strtoupper('success'))
                                {
                                    $date = $this->date->gmtDate();
                                    $subscription_history_detail->setData("status_success", "Unsubscribed");
                                    $subscription_history_detail->setData("update_time", $date);
                                    $subscription_history_detail->save();

                                    $this->UnSubscribeChild($subscription_history_detail,"Unsubscribed");

                                    $this->getCustomMailSend(1,$subscription_history_detail);
                                }
                                else
                                {
                                    if($flag != 'deleteacc'){                                       
                                        $this->getCustomMailSend(0,$subscription_history_detail);   
                                    }
                                }
                                /*Custom code ends*/
                        }
                    }
                    
                }

                if($flag != 'deleteacc'){
                    $field4 =  $this->EmarsysHelper->isApiEnabled();
                    if($field4)
                    {
                        if ($this->getRequest()->getParam('unsub_crondata')) {
                            $customer_id = $this->getRequest()->getParam('customer_id');
                            $contact = array(
                                    "485"=>$customer_id,
                                    "490"=>2
                                   );
                        }else{
                            $contact = array(
                                    "485"=>$customersession->getCustomer()->getId(),
                                    "490"=>2
                                   );
                        }

                        $encode = json_encode($contact);
                        $apiHelper = $this->emarsysApiHelper;
                        $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    }
                    //Zoho CRM integration start//
                    if($this->zohoCRMHelper->isEnabled()){
                        $subscriptionData = array(
                            "Priority"=>"3",
                            "Comment"=> "Edit Customer Subscription",
                            "Customer_Type"=>"Unsubscribed"
                        );
                        if ($this->getRequest()->getParam('unsub_crondata')) {
                            $customer_id = $this->getRequest()->getParam('customer_id');
                            $this->zohoCRMHelper->editCustomer($subscriptionData,$customer_id);
                        }else{
                            $this->zohoCRMHelper->editCustomer($subscriptionData,$customersession->getCustomer()->getId());
                        }
                    }
                    //Zoho CRM integration end//
                }
            }
            else
            {
                $this->__messageManager->addError('Unable to Unsubscribe.');
            }
        }
        else
            $this->__messageManager->addNotice('You have already requested for Unsubscribe.');
        
 
        //$isDelete = $this->session->getIsDelete();
        
        
        
        if($flag == null && $pflag == null){
           $url='subscription/index/list';
             return $url;   
        }     
        else if($flag == 'deleteacc'){
            $url='deleteaccount/index/finalizedelete'; 
            return $url;      
        }   
        else if($pflag == 'adminunsub'){

            $this->_backendAuthSession->unsadmincustomer();
            $this->_backendAuthSession->unsadmincustomer1();

             if($this->payment_method == 'paypal'){
                $url='unsubscribepaypal/index/unsubscribefinal';
                return $this->_helper->getUrl($url);
               // $this->_redirect($this->_urlInterface->getBaseUrl()."admin/unsubscribepaypal/adminhtml/index/unsubscribefinal");    
            }
            else if($this->payment_method == 'tco'){
                        
                 $url='unsubscribetco/index/unsubscribefinal';

                return $this->_helper->getUrl($url);
                //$this->_redirect($this->_urlInterface->getBaseUrl()."admin/unsubscribetco/index/unsubscribefinal");
            }
        }
    }
    
    public function getCustomMailSend($unsubscribed,$subscription_history_detail)
    {       
        
        if ($this->getRequest()->getParam('unsub_crondata')) {
            $customer_id = $this->getRequest()->getParam('customer_id');
            $customer_email = $this->getRequest()->getParam('customer_email');
        }else{
            //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            //$customersession  = $objectManager->get('\Magento\Customer\Model\Session');
            $customersession  = $this->session;
            $customer_id = $customersession->getCustomer()->getId();
            $customer_email = $customersession->getCustomer()->getEmail();
        }
        
        $msg = "Customer Id :: ".$customer_id."\n";
        $msg .= "Customer Email :: ".$customer_email."\n";
        $msg .= "Start Date :: ".$subscription_history_detail->getFromDate()."\n";
        $msg .= "End Date :: ".$subscription_history_detail->getToDate()."\n";
        $msg .= "Period :: ".$subscription_history_detail->getSubscriptionPeriod()."\n";
        
        if(!empty($unsubscribed))
        {
            $msg .= "Already Unsubscribed Using Api"."\n";
            $subjectMail = "Unsubscribed Successfully";
        }
        else
        {
          $msg .= "Request :: Request for Unsubscription"."\n";
          $subjectMail = "Unsubscribe Manually";
          $this->__messageManager->addSuccess('Unsubscribe Request successfully sent to Admin.');
        }
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        // Additional headers
        $headers[] = 'From: support@slideteam.net';

        /*mail("rahulgosain@slideteam.net","2Checkout Unsubscription Request",$msg);*/
        mail("geetika.gosain@slideteam.net",$subjectMail,$msg,implode("\r\n", $headers));
        mail("uminfy@yahoo.com",$subjectMail,$msg,implode("\r\n", $headers));
        mail("ron@slideteam.net",$subjectMail,$msg,implode("\r\n", $headers));
        mail("krunal.vakharia@tatvasoft.com",$subjectMail,$msg,implode("\r\n", $headers));
        
        
    }

    public function getLineItemIds($sale_id)
    {
         $client = $this->httpClientFactory->create();
        $path = 'sales/detail_sale';
        $url = $this->_checkout->getApiUrl();
        $client->setUri($url . $path);

        $client->setConfig(['maxredirects' => 0, 'timeout' => 60]);

        $client->setAuth($this->_checkout->getConfigData('api_user'), $this->_checkout->getConfigData('api_pass'));
        $args = array(
            'sale_id' => $sale_id,
        );
        $client->setHeaders(
           [
               'Accept: application/json'
           ]
        );

        $client->setParameterPost($args);
        $client->setMethod(\Zend_Http_Client::POST);
 
        try {
            $response = $client->request();
            $responseBody = json_decode($response->getBody(), true);            
            $line_item_ids=[];
            
            if(is_array($responseBody)){

                if ($responseBody['response_code'] && $responseBody['response_code'] == "OK")
                {
                    $sale = $responseBody['sale'];
                    $invoices = $sale['invoices'];
                    if (is_array($invoices) && count($invoices) > 0)
                    {
  
                        foreach ($invoices as $in)
                        {
                            //if ($in['recurring'] == "1") {

                                $line_items =$in['lineitems'];
                                if (is_array($line_items) && count($line_items) > 0)
                                {
                                    foreach ($line_items as $li)
                                    {
                                        if($li['billing']['recurring_status']== "active")
                                            $line_item_ids[] = $li['lineitem_id'];
                                    }
                                }
                            //}                          
                        }
                    }
                }
                
                return $line_item_ids;
            }
            elseif (isset($responseBody['errors'])) {
                $this->_logger->critical(sprintf('Error Refunding Invoice: "%s"', $responseBody['errors'][0]['message']));
                throw new \Magento\Framework\Exception\LocalizedException(__($responseBody['errors'][0]['message']));
            } elseif (!isset($responseBody['response_code']) || !isset($responseBody['response_message'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Error refunding transaction.'));
            } elseif ($responseBody['response_code'] != 'OK') {
                throw new \Magento\Framework\Exception\LocalizedException(__($responseBody['response_message']));
            }
            elseif ($responseBody['response_code'] == 'OK') {
                throw new \Magento\Framework\Exception\LocalizedException(__($responseBody['response_message']));
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }


    }

    public function UnSubscribeChild($subscription_history_detail,$status)
    {
        $date = $this->date->gmtDate();
        $sub_period_lable = $subscription_history_detail->getSubscriptionPeriod();
        $team_plan_array = $this->_teamplanModel->getTeamSubscriptionPlans();

        if(in_array($sub_period_lable,$team_plan_array))
        {
            $parentid = $subscription_history_detail->getCustomerId();
            $child_customers = $this->SubscriptionHelper->getChildSubscriptionHistory($parentid);

            foreach ($child_customers as $child) {

                if(empty($child->getChildCustomerId())) :
                    continue;
                endif;

                $customer_id = $child->getChildCustomerId();
                $subscription_history_detail = $this->_subscription->getLastPaidSubscriptionhistory($customer_id); 

                if($subscription_history_detail == "Unsubscribed" && $status == "Unsubscribed") :
                    continue;
                endif;

                $subscription_history_detail->setData("status_success", $status);
                $subscription_history_detail->setData("update_time", $date);
                $subscription_history_detail->save();
            }
        }
        
    }

    public function stopRecurring($is_sandbox, $api_username, $api_password, $headers, $line_item_id)
    {

        $ch = curl_init();
        $post_fields = array("lineitem_id" => $line_item_id);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, "$api_username:$api_password");
        $stop_recurring_url = "https://" . (($is_sandbox) ? "sandbox" : "www") . ".2checkout.com/api/sales/stop_lineitem_recurring";
        curl_setopt($ch, CURLOPT_URL, $stop_recurring_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $result = json_decode($result);
        curl_close($ch);
        return $result;
    }

    public function sendUnsubscriptionRequestMailToAdmin($subscription_history_detail, $order, $unsubscribed = false)
    {
        $subscription_history_id = $subscription_history_detail->getId();        
        
        $vars = array();
        $vars['subscription_id'] = $this->_subscription->unsubscribe_id($subscription_history_id);
        $vars['starting_date'] = $subscription_history_detail->getFromDate();
        $vars['ending_date'] = $subscription_history_detail->getToDate();
        $vars['period'] = $subscription_history_detail->getSubscriptionPeriod();
        $vars['order'] = $order;
        $vars['unsubscription_message'] = ($unsubscribed ? "Unsubscribed Successfully" : "Request for Unsubscription");
        
       
        $paymentBlockHtml = "";
        $storeId = $this->_storeManager->getStore()->getId();
        $mailTemplate =  $this->sendemail;   
        if (isset($vars['order']) && (!empty($vars['order'])))
        {
            $paymentBlock = $this->_paymentHelper->getInfoBlock($vars['order']->getPayment())->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
            $vars['payment_html'] = $paymentBlockHtml;
        }           
        $mail_status = $mailTemplate->sendEmail("1",$storeId, $vars , array("email" => $this->_scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE), "name" => $this->_scopeConfig->getValue("trans_email/ident_sales/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)),$vars['order']->getCustomerEmail(),$vars['order']->getCustomerFirstname() . " " . $vars['order']->getCustomerLastname());
        //$mail_status = $mailTemplate->sendEmail("1",$storeId, $vars , array("email" => "krunal.vakharia@tatvasoft.com", "name" => $this->_scopeConfig->getValue("trans_email/ident_sales/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)),$vars['order']->getCustomerEmail(),$vars['order']->getCustomerFirstname() . " " . $vars['order']->getCustomerLastname());

        $this->_inlineTranslation->resume();
        $mailStatus=0;
        if($mail_status!== NULL)
        {
            $mailStatus=$mail_status->getSentSuccess();
        }
                
        if ($mailStatus)
        {

            if (!$unsubscribed)
            {
                $subscription_history_detail->setStatusSuccess("Requested Unsubscription");
                $subscription_history_detail->setUserStatusUnsubscribe("1");
                $subscription_history_detail->setUserStatusUnsubscribeDate(date("Y-m-d"));
                $subscription_history_detail->save();

                $this->__messageManager->addSuccess('Unsubscribe Request successfully sent to Admin.');
            }
        }
        else
        {
            $this->__messageManager->addError('We encountered an error in unsubscribing you. We apologize. Please contact support at support@slideteam.net');
            //$this->__messageManager->addError('Because of Unsubscription Mail hasn\'t sent to admin, you request has not been accepted.');
        }
    }

    public function sendUnsubscribeEmail($subscription_history_detail, $order, $profileId)
    {
        $subscription_history_id = $subscription_history_detail->getId();
        $mailTemplate = $this->sendemail;
        $vars = array();
        $vars['profileId'] = $profileId;
        $vars['subscription_id'] = $profileId;
        $vars['starting_date'] = $subscription_history_detail->getFromDate();
        $vars['ending_date'] = $subscription_history_detail->getToDate();
        $vars['period'] = $subscription_history_detail->getSubscriptionPeriod();
        $vars['order'] = $order;
        $vars['unsubscription_message'] = "Unsubscibed Successfully";

        $paymentBlockHtml = "";
        $storeId = $this->_storeManager->getStore()->getId();
        if (isset($vars['order']) && (!empty($vars['order'])))
        {
            $paymentBlock = $this->_paymentHelper->getInfoBlock($vars['order']->getPayment())->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
            $vars['payment_html'] = $paymentBlockHtml;
        }
       $mail_status = $mailTemplate->sendEmail("4",$storeId, $vars , array("email" => $this->_scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE), "name" => $this->_scopeConfig->getValue("trans_email/ident_sales/name")), $vars['order']->getCustomerEmail(),$vars['order']->getCustomerFirstname() . " " . $vars['order']->getCustomerLastname());


     $this->_inlineTranslation->resume();
     $mailStatus=0;
     if($mail_status!== NULL)
     {
        $mailStatus=$mail_status->getSentSuccess();
      }
        if ($mailStatus)
        {
            //same email will sent to sales@slideteam.net from support@slideteam.net
            
            $mailTemplate->sendEmail("4",$storeId, $vars , array("email" => $this->_scopeConfig->getValue("trans_email/ident_support/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE), "name" => $this->_scopeConfig->getValue("trans_email/ident_sales/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)), $this->_scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),$this->_scopeConfig->getValue("trans_email/ident_sales/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

            $subscription_history_detail->setStatusSuccess("Requested Unsubscription");
            $subscription_history_detail->setUserStatusUnsubscribe("1");
            $subscription_history_detail->setUserStatusUnsubscribeDate(date("Y-m-d"));
            $subscription_history_detail->save();
            $this->__messageManager->addSuccess('Processing your request');
        }
        else
        {
            $this->__messageManager->addSuccess('Unsubscribe Request successfully,Your request is Processing');
        }
    }
}

?>