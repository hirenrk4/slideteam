<?php
namespace Tatva\Paypalrec\Model;

class Unsubscribe extends \Magento\Framework\Model\AbstractModel
{   

    /**
     * @var string $payment_method
     */
    protected $_paymentMethod;

    protected $_subscription;

    protected $_ins;

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Tatva\Subscription\Model\Subscription $_subscriptionSubscriptionFactory,
        \Tco\Checkout\Model\Ins $_ins,
        \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Tco\Checkout\Model\Checkout $_checkout,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\RecurringPayments\Api\Subscription\RepositoryInterface $subscriptionRepository,
        \Amasty\RecurringPayments\Model\Subscription\Operation\SubscriptionCancelOperation $subscriptionCancelOperation,
        \Tatva\Paypalrec\Model\ResourceModel\Result\CollectionFactory $_paypalrecResultCollectionFactory,
        \Tatva\Paypalrec\Model\ResourceModel\PaypalRecurringMapper\CollectionFactory $_paypalrecMapperResultCollectionFactory,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Tatva\Catalog\Model\Productdownloadhistorylog $productdownloadhistorylog,
        \Tatva\Deleteaccount\Model\DeletedcustomerbkpFactory $deletedcustomerbkp,
        \Tatva\Deleteaccount\Model\SubscriptionbkpFactory $subscriptionbkp,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $salesrulecollection,
        array $data = []
    ) {
        
        $this->_subscription = $_subscriptionSubscriptionFactory;
        $this->_ins = $_ins;
        $this->_scopeConfig = $_scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
        $this->_checkout = $_checkout;
        $this->_logger = $logger;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionCancelOperation = $subscriptionCancelOperation;
        $this->_paypalrecResultCollectionFactory = $_paypalrecResultCollectionFactory;
        $this->_paypalrecMapperResultCollectionFactory = $_paypalrecMapperResultCollectionFactory;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
        $this->zohoCRMHelper = $zohoCRMHelper;
        $this->_customerModel = $customerModel;
        $this->_dateTime = $dateTime;
        $this->_productdownloadhistorylog = $productdownloadhistorylog;
        $this->_deletedcustomerbkp = $deletedcustomerbkp;
        $this->_subscriptionbkp = $subscriptionbkp;
        $this->salesrulecollection = $salesrulecollection;
    }

    public function Unsubscribe($customerId)
    {
        //$subscriptionId = $subscriptionId;
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fb_del.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Start Unsubscribe");

        $customer_id = $customerId;    
        
        $subscription_history_detail = "";
        $subscription_history_detail = $this->_subscription->getLastPaidSubscriptionhistory($customer_id);
                   
        if (is_object($subscription_history_detail) && $subscription_history_detail !== "Unsubscribed")
        {
            $subscription_history_id = $subscriptionId = $subscription_history_detail->getId();
 
            if (isset($subscriptionId) && $subscriptionId != "" && $subscriptionId == $subscription_history_id)
            {
                $two_checkout_message_id = $subscription_history_detail->getTwoCheckoutMessageId();
                $stripe_checkout_message_id = $subscription_history_detail->getStripeCheckoutMessageId();

                if ($two_checkout_message_id != "" && $two_checkout_message_id != "0")
                {
                    $this->payment_method = 'tco';

                    $two_checkout_model = $this->_ins->load($two_checkout_message_id);

                    if (is_object($two_checkout_model) && $two_checkout_model->getSaleId() != "")
                    {
                        $unsubscribed = false;
                        $recurring_stopped = false;
                        $headers = array("Accept: application/json",);
                         
                        
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
                                           
                                            $date = $this->date->gmtDate();
                                            $checkout_ins_id = $two_checkout_message_id;
                                            $subscription_history_detail->setData("two_checkout_message_id", $two_checkout_message_id);
                                            $subscription_history_detail->setData("status_success", "Unsubscribed");
                                            $subscription_history_detail->setData("update_time", $date);
                                            $subscription_history_detail->save();
                                            $unsubscribed = true;
                                        }
                                        else
                                        {
                                            $this->getCustomMailSend($unsubscribed,$subscription_history_detail,$customer_id);
                                        }
                                    }
                                    
                                }
                            }
                        }
                        else
                        {
                            $this->getCustomMailSend($unsubscribed,$subscription_history_detail,$customer_id);                         
                        }
                    }
                } elseif (!empty($stripe_checkout_message_id)){
                    $this->payment_method = 'stripe';
                    $unsubscribed = false;
                    $logger->info("1");
                    try {
                        $subscription = $this->subscriptionRepository->getBySubscriptionId($stripe_checkout_message_id);
                    } catch (NoSuchEntityException $e) {
                        $subscription = null;
                    }
                    $logger->info("2");
                    $customerId = $customer_id;
                    if ($subscription && $subscription->getCustomerId() == $customerId) {
                        $unsubscribed = $this->subscriptionCancelOperation->execute($subscription);
                        $logger->info("3");
                        $date = $this->_dateTime->gmtDate();
                        $subscription_history_detail->setData("status_success", "Unsubscribed");
                        $subscription_history_detail->setData("update_time", $date);
                        $subscription_history_detail->save();
                        $logger->info("4");
                        $this->getCustomMailSend(true,$subscription_history_detail,$customer_id);
                        $logger->info("5");
                    }
                }
                else
                {
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
                                $api_username = "tatvasoft186-facilitator-us-pro_api1.gmail.com";
                                $api_password = "5H7H4PLCM273A7DR";
                                $api_signature = "A-8sxAsepLwVCTE9rkoV7Fc5m-kFAvAepSmgvDyuzoAcAmR9vkOU2Adl";
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
                                    $this->getCustomMailSend(1,$subscription_history_detail,$customer_id);
                                }
                                else
                                {
                                    $this->getCustomMailSend(0,$subscription_history_detail,$customer_id);
                                }
                                /*Custom code ends*/
                        }
                    }                    
                }

                $logger->info("Emarsys Start");
                $field4 =  $this->EmarsysHelper->isApiEnabled();
                if($field4)
                {
                    $contact = array(
                            "485"=>$customer_id,
                            "490"=>2
                           );
                    $encode = json_encode($contact);
                    $apiHelper = $this->emarsysApiHelper;
                    $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                }
                $logger->info("Emarsys End");

                $logger->info("Zoho Start");
                //Zoho CRM integration start//
                if($this->zohoCRMHelper->isEnabled()){
                    $subscriptionData = array(
                        "Priority"=>"3",
                        "Comment"=> "Edit Customer Subscription",
                        "Customer_Type"=>"Unsubscribed"
                    );
                    $this->zohoCRMHelper->editCustomer($subscriptionData,$customer_id);
                }
                //Zoho CRM integration end//
                $logger->info("Zoho End");
            }
            
        }
       
        //$url='deleteaccount/index/finalizedelete';  
        
        
        $logger->info("After Unsubscribe");
        
                   
        $this->DeleteAccountBefore($customer_id);
       
    }

    public function DeleteAccountBefore($customer_id)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fb_del.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Delete Start Step 1");
        
        $feedback = array();
        $customer_data = $this->getCustomerData($customer_id);
        $logger->info("Delete Start Step 2");
        $subscription_details = $this->getSubscriptionDetails($customer_id);
        $logger->info("Delete Start Step 3");
        $customer_saved_bkp_tbl = $this->saveCustomer2DelTbl($customer_data,$feedback);
        $logger->info("Delete Start Step 4");
        $customer_subscription_bkp_tbl = $this->saveCustomerSubscriptionTbl($subscription_details);
        $logger->info("Delete Start Step 5");


        //$current_loggedIn_customer = $this->_customerSession->getCustomer();
        //$customer_id =  $current_loggedIn_customer->getId();
        $collection = $this->salesrulecollection->create();
        $collection->getSelect()->joinInner(array('src'=>'coupon_customer_relation'),'src.sales_rule_id=main_table.rule_id',array('src.sales_rule_id','src.customer_id'));
        $collection->addFieldToFilter('src.customer_id',$customer_id);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('main_table.rule_id');
        foreach ($collection as $item) 
        {
            $item->delete();
        }
        $logger->info("Delete Start Step 6");
    }

    public function getCustomerData($customer_id)
    {
        $current_loggedIn_customer = $this->_customerModel->load($customer_id);
        $customer_id =  $current_loggedIn_customer->getId();
        $deletedDate = $this->_dateTime->gmtDate();
        $customer_data = array();
        $customer_data = $current_loggedIn_customer->getData();
        $customer_data['deleted_date'] = $deletedDate;

        return $customer_data;
    }

    public function getSubscriptionDetails($customer_id)
    {
        //$current_loggedIn_customer = $this->_customerSession->getCustomer();
        //$customer_id =  $current_loggedIn_customer->getId();
        
        $status_success = 'Paid';
        $subscription_data = array();
        $collection = $this->_subscription->getCollection();
        $collection->addFieldToFilter('main_table.customer_id',$customer_id);
        $collection->addFieldToFilter('main_table.status_success',$status_success);
        $collection->addFieldToSelect('subscription_period'); 
        $collection->addFieldToSelect('paypal_id'); 
        $collection->addFieldToSelect('two_checkout_message_id'); 
        $collection->addFieldToSelect('from_date'); 
        $collection->addFieldToSelect('to_date'); 
        $collection->addFieldToSelect('customer_id');
        $subscription_counter = 0;

        foreach ($collection as $item)
        {
            $item['payment_method'] = $this->getPaymentMethod($item);   
            $subscriptions_c = $collection;
            $join_tbl = '';
            $main_tbl_pay_id = '';
            $payable_subscription = true;

            if($item['payment_method'] == 'Paypal')
            {
                $main_tbl_pay_id = 'paypal_id';
                $join_tbl = 'paypal_result';                
                $join_amount_col = 'amount';    
            }
            else if($item['payment_method'] == 'Two Checkout')
            {
                $main_tbl_pay_id = 'two_checkout_message_id';       
                $join_tbl = '2checkout_ins';
                $join_amount_col = 'invoice_cust_amount';
            }
            else if($item['payment_method'] == 'Paid Directly')
            {
                $payable_subscription = false;
                $data_with_amount_method[$subscription_counter]['Amount_Paid'] = 'Paid Directly';
            }

            if($payable_subscription == true)
            {
                $subscriptions_c->getSelect()->joinLeft(array('joined_tbl'.$subscription_counter => $join_tbl),
                    'main_table.'.$main_tbl_pay_id.' = joined_tbl'.$subscription_counter.'.id',
                    array( 'joined_tbl'.$subscription_counter.'.'.$join_amount_col.' AS Amount_Paid')); 

                $data_with_amount_method = $subscriptions_c->getData();
            }

            $subscription_data[$subscription_counter] = $item->getData(); 
            $subscription_data[$subscription_counter]['payment_method'] = $item['payment_method'];
            $subscription_data[$subscription_counter]['amount'] = $data_with_amount_method[$subscription_counter]['Amount_Paid'];
            $subscription_data[$subscription_counter]['downloads'] = $this->getDownloadCounts($customer_id,$item['from_date'],$item['to_date']);
            $subscription_counter++;
        }
        return $subscription_data;
    }

    public function getPaymentMethod($subscriptionData)
    {
        $payment_method = '';
        if($subscriptionData['paypal_id'] != null)
            $payment_method = 'Paypal';

        else if($subscriptionData['two_checkout_message_id'] != null)
            $payment_method = 'Two Checkout';

        else if($subscriptionData['paypal_id'] == null && $subscriptionData['two_checkout_message_id'] == null )
            $payment_method = 'Paid Directly';

        return $payment_method;
    }

    public function getDownloadCounts($customer_id ,$start_date ,$end_date )
    {
        $downloads = 0;
        $collection = $this->_productdownloadhistorylog->getCollection();
        $collection->addFieldToFilter('main_table.customer_id',$customer_id);
        $collection->addFieldToFilter('main_table.download_date', array('gteq'=>$start_date));
        $collection->addFieldToFilter('main_table.download_date', array('lteq'=>$end_date));
        $collection->getSelect()->columns('COUNT(DISTINCT(product_id)) AS downloads');
        $download_data = $collection->getData();
        $downloads = $download_data[0]['downloads'];

        return $downloads;
    }

    public function saveCustomer2DelTbl($customerData,$feedback)
    {
        $data = array(
            'customer_id'=>$customerData['entity_id'],
            'email_id'=>$customerData['email'],
            'firstname'=>$customerData['firstname'],
            'lastname'=>$customerData['lastname'],
            'created_date'=>$customerData['created_at'],
            'deleted_date'=>$customerData['deleted_date']
        );
        
        $deletedCustomer_model = $this->_deletedcustomerbkp->create();
        $deletedCustomer_model->setData($data);
        $saved_obj = $deletedCustomer_model->save();

        $field4 = $this->_scopeConfig->getValue('button/emarsys_config/field3',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($field4) {
            $apiHelper = $this->emarsysApiHelper;
            $result = $apiHelper->send('POST', 'contact/delete', '{"key_id": "485","485":"'.$customerData['entity_id'].'"}');
        }

        //Zoho CRM integration start//
        if($this->zohoCRMHelper->isEnabled()){
            $subscriptionData = array(
                "Priority"=>"3",
                "Comment"=> "Deleted Customer",
                "Customer_Action"=>"Account Deleted"
            );
            $this->zohoCRMHelper->editCustomer($subscriptionData,$customerData['entity_id']);
        }
        //Zoho CRM integration end//

        return $saved_obj;
    }

    public function saveCustomerSubscriptionTbl($subscriptionData)
    {
        $delCustomerSub_model = $this->_subscriptionbkp->create();
        
        foreach($subscriptionData as $subscription)
        {
            $data = array ('del_customer_id'=>$subscription['customer_id'],
                'subscription_period'=>$subscription['subscription_period'],
                'amount_paid'=>$subscription['amount'],
                'payment_method'=>$subscription['payment_method'],
                'start_date'=>$subscription['from_date'],
                'end_date'=>$subscription['to_date'],
                'downloads'=>$subscription['downloads']
            );
            
            $delCustomerSub_model->setData($data);
            $saved_obj = $delCustomerSub_model->save();
        }
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

                if (isset($responseBody['response_code']) && $responseBody['response_code'] == "OK")
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

    public function getCustomMailSend($unsubscribed,$subscription_history_detail,$customer_id)
    {   
        $customersession = $this->_customerModel->load($customer_id);
            
        $msg = "Customer Id :: ".$customer_id."\n";
        $msg .= "Customer Email :: ".$customersession->getEmail()."\n";
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
          //$this->__messageManager->addSuccess('Unsubscribe Request successfully sent to Admin.');
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
}

?>
