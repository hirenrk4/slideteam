<?php
namespace Tatva\Subscription\Model;


class Ipncheck
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected   $_logger;

    /**
     * @var \Tatva\Subscription\Model\SubscriptionhistoryFactory
     */
    protected   $_subscription;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected   $_orderFactory;

    protected   $_subscriptionCollection;

    protected   $_postData;

    protected   $_paymentMethod;

    /**
     * @var \Tatva\Paypalrec\Model\ResourceModel\Result\Collection
     */
    protected   $_paypalrecResultCollection;


    /**
     * @var \Magento\RecurringPayment\Model\PaymentFactory
     */
    protected $_recurringPaymentFactory;

    protected $_ppRecurringMapperFactory;

    /**
     * Recurring payment instance
     *
     * @var \Magento\RecurringPayment\Model\Payment
     */
    protected $_recurringPayment;
    /**
     * $EmarsysHelper
     *
     * @type \Tatva\Emarsys\Helper\Data
     */
    protected $EmarsysHelper;
    /**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;
    protected $_deletedcustomerbkp;
    protected $_recurringpayment;
    
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Psr\Log\LoggerInterface $logger,
        \Tatva\Subscription\Model\SubscriptionFactory $subscription,
        \Tatva\Subscription\Model\ResourceModel\Subscription\CollectionFactory  $subscriptionCollection,
        \Tatva\Paypalrec\Model\ResourceModel\Result\Collection $paypalrecResultCollection,
        \Magento\RecurringPayment\Model\PaymentFactory $recurringPaymentFactory,
        \Tatva\Paypalrec\Model\PaypalRecurringMapperFactory $ppRecurringMapperFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepositoryFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Tatva\Deleteaccount\Model\DeletedcustomerbkpFactory $deletedcustomerbkp,
        \Magento\Framework\Registry $registry,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
        \Tatva\RecurringOrders\Model\Recurringpayment $recurringpayment,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Tatva\Subscription\Helper\Data $SubscriptionHelper,
        \Tatva\Subscription\Model\Subscription $subscrptionModel,
        \Tatva\Subscription\Helper\TeamPlans $teamplanModel
    ) {
        $this->_deletedcustomerbkp = $deletedcustomerbkp;
        $this->_registry = $registry;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->_logger = $logger;
        $this->_subscriptionCollection = $subscriptionCollection;
        $this->_subscription = $subscription;
        $this->_subscriptionModel = $subscrptionModel;
        $this->_orderFactory = $orderFactory;
        $this->_paypalrecResultCollection = $paypalrecResultCollection; 
        $this->_recurringPaymentFactory = $recurringPaymentFactory;
        $this->_ppRecurringMapperFactory = $ppRecurringMapperFactory;
        $this->scopeConfig         = $scopeConfig;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->customerRepository = $customerRepositoryFactory->create();
        $this->customerFactory = $customerFactory->create();
        $this->_resourceConnection = $resourceConnection;
        $this->date = $date;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->zohoCRMHelper = $zohoCRMHelper;
        $this->_storeManager = $_storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->emarsysApiHelper = $emarsysApiHelper;
        $this->_recurringpayment = $recurringpayment;
        $this->SubscriptionHelper = $SubscriptionHelper;
        $this->_teamplanModel = $teamplanModel;
    }

    /**
     * IPN request data getter
     *
     * @param string $key
     * @return array|string
     */
    public function getRequestPostData($key = null)
    {
        if (null === $key) {
            return $this->_postData;
        }
        return isset($this->_postData[$key]) ? $this->_postData[$key] : null;
    }

    /**
     * 1 - Add new entry in subscription_history
     * 2 - Update data in GA 
     * 3 - Update data in Emarsys (May be this will be not needed as we are going to use emarsys extention)
     *
     * @todo  Need to work for cancel subscription
     */
    public function saveSubscription($postData, $paypal_result_id = NULL, $checkout_ins_id = NULL, $txn_id = NULL, $txn_type = NULL){

        $recurringExecuted = 0;
        $date = $this->_dateFactory->create()->gmtDate();
        $this->_postData = $postData;
        // Check whether this is new order or recurring and use proper flow
        try {
            // Subscription Paid by Paypal id of paypal_result
            if (!empty($paypal_result_id) && empty($checkout_ins_id)) {

                $this->_paymentMethod = "paypal";
                $order = $this->getSubscriptionOrder();

                $sub_payment_status = $this->getPaymentStatusPaypal();
                $m2_success_order_txn_types = ['express_checkout','cart','recurring_payment','recurring_payment_profile_created'];
                $is_m2_success_order_ipn = in_array($txn_type, $m2_success_order_txn_types);
                $customerIp = $order->getRemoteIp();

                $order_id = $order->getId();

                if($txn_type == "recurring_payment")
                {   
                    $this->_recurringpayment->Recurringpayment($order_id);
                }

                if(isset($this->_postData['initial_payment_status']))
                {
                    $init_status = $this->_postData['initial_payment_status'];
                    if ($txn_type == "recurring_payment_profile_created" && $init_status == "Completed" ) {

                        $order_id = $order->getId();

                        /*if($txn_type == "recurring_payment")
                        {
                            $this->_recurringpayment->Recurringpayment($order_id);
                        }*/

                        if(isset($postData['customer_email']))
                        {
                            $customerEmail = $postData['customer_email'];
                        }
                        elseif(isset($postData['payer_email']))
                        {
                            $customerEmail =  $postData['payer_email'];   
                        }

                        if ($order_id != "") {
                            $order->setStatus("payment_completed");
                            $order->save();
                        }
                        else
                        {
                            $vars = array();
                            $ipnData = '';
                            foreach ($postData as $key => $value) {
                                $ipnData.='<span>'.$key.'=>'.$value.'</span><br/>';
                            }
                            $vars['postData'] = $ipnData;
                            $vars['paypal_message'] = "Change order status to Payment Completed for ".$customerEmail;
                            
                            $templateId = $this->scopeConfig->getValue("subscription_options/paypal_order_email/not_completed_template", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            
                            $storeId = $this->_storeManager->getStore()->getId();
                            
                            $from = array("email" => $this->scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE), "name" => $this->scopeConfig->getValue("trans_email/ident_sales/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                            
                            $to = $this->scopeConfig->getValue("subscription_options/paypal_order_email/to_email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            $cC = $this->scopeConfig->getValue("subscription_options/paypal_order_email/cc_email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                            $emails = explode(",",$cC);
                            $toEmails = explode(",", $to);

                            if($templateId){                                
                                $transport = $this->_transportBuilder
                                    ->setTemplateIdentifier($templateId)
                                    ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId))
                                    ->setTemplateVars($vars)
                                    ->setFrom($from);

                                    if(!empty($toEmails))
                                    {
                                        foreach ($toEmails as $value) {
                                            $this->_transportBuilder->addTo($value);
                                        }
                                    }

                                    if(!empty($emails))
                                    {
                                        foreach($emails as $email)
                                        {
                                            $this->_transportBuilder->addCc($email);
                                        }
                                    }
                                $transport = $this->_transportBuilder->getTransport();
                                try{
                                     $transport->sendMessage();
                                }
                                catch(Exception $e){
                                    return $e->getMessage();
                                }
                            }
                        }
                        
                    } elseif ($txn_type == "recurring_payment_profile_created" && $init_status != "Completed") {
                        $vars = array();
                        $ipnData = '';
                        foreach ($postData as $key => $value) {
                            $ipnData.='<span>'.$key.'=>'.$value.'</span><br/>';
                        }

                        $vars['order'] = $order;
                        $vars['postData'] = $ipnData;
                        $vars['paypal_message'] = "Not Completed Paypal Order Information";

                        $templateId = $this->scopeConfig->getValue("subscription_options/paypal_order_email/template_id", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                        $storeId = $this->_storeManager->getStore()->getId();
                        
                        $from = array("email" => $this->scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE), "name" => $this->scopeConfig->getValue("trans_email/ident_sales/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                        $to = $this->scopeConfig->getValue("subscription_options/paypal_order_email/to_email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $cC = $this->scopeConfig->getValue("subscription_options/paypal_order_email/cc_email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        
                        $emails = explode(",",$cC);
                        $toEmails = explode(",", $to);

                        if($templateId){
                            $transport = $this->_transportBuilder
                                ->setTemplateIdentifier($templateId)
                                ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId))
                                ->setTemplateVars($vars)
                                ->setFrom($from);

                                if(!empty($toEmails))
                                {
                                    foreach ($toEmails as $value) {
                                        $this->_transportBuilder->addTo($value);
                                    }
                                }

                                if(!empty($emails))
                                {
                                    foreach($emails as $email)
                                    {
                                        $this->_transportBuilder->addCc($email);
                                    }
                                }
                            $transport = $this->_transportBuilder->getTransport();
                            try{
                                 return $transport->sendMessage();
                            }
                            catch(Exception $e){
                                return $e->getMessage();
                            }
                        }
                    }
                    else
                    {
                        exit;
                    }
                }
                

                if ($txn_type == "subscr_signup") {
                    $order_id = $order->getId();
                    if ($order_id != "") {
                        $order->setStatus("payment_completed");
                        $order->save();
                    }
                }

                if ( $order && $txn_type == "subscr_payment" ) {

                    $alreadyPaidSubscription = $this->_subscriptionCollection->create()
                                                ->addFieldToFilter("txn_id", $txn_id)->getFirstItem();

                    // Recurring Payment
                    if (is_object($alreadyPaidSubscription) && $alreadyPaidSubscription->getId() != "") {
                        $alreadyPaidSubscription->setData("status_success",$sub_payment_status);
                        $alreadyPaidSubscription->setData("update_time", $date);
                        $alreadyPaidSubscription->save();

                        $this->UpdateChildSubscription($alreadyPaidSubscription,$sub_payment_status,0);
                    }
                    else{
                        // For new subscription
                        // Need to implement old savaSubscription here
                        $this->createNewSubscription($sub_payment_status, $paypal_result_id,$checkout_ins_id,$txn_id);
                    }

                }else if ( $order && $is_m2_success_order_ipn) {

                    // If IPN received second time for same transaction
                    $subscriptionCollection = $this->_subscriptionCollection->create()
                            ->addFieldToFilter("increment_id", $order->getIncrementId())
                            ->addFieldToFilter("customer_id", $order->getCustomerId())
                            ->addFieldToFilter("txn_id", $txn_id);

                    $subscriptionCollection->getSelect()->order('subscription_history_id desc')->limit(1);

                    // Recurring Payment this is not usual case
                    if (is_object($subscriptionCollection) && count($subscriptionCollection) > 0 ) {
                        foreach ($subscriptionCollection as $subscription) {
                            $newSubscription = $this->_subscription->create()->load($subscription->getId());
                            $newSubscription->setStatusSuccess($sub_payment_status);
                            $newSubscription->setPaypalId($paypal_result_id);
                            $newSubscription->setUpdateTime($date);
                            $newSubscription->save();

                            $this->UpdateChildSubscription($newSubscription,$sub_payment_status,0);
                        }
                    }
                    else{
                        // For new subscription's create_recurring_profile IPN which don't have txn but to allow customer to download immediately.
                        $newSubscriptionCollection = $this->_subscriptionCollection->create()
                            ->addFieldToFilter("increment_id", $order->getIncrementId())
                            ->addFieldToFilter("customer_id", $order->getCustomerId())
                            ->addFieldToFilter("txn_id",["null"=>true])
                            ->addFieldToFilter("paypal_id",["notnull"=>true]);
                        $newSubscriptionCollection->getSelect()->order('subscription_history_id desc')->limit(1);
                        if (is_object($newSubscriptionCollection) && count($newSubscriptionCollection) > 0 ) {
                            foreach ($newSubscriptionCollection as $subscription) {
                                $newSubscription = $this->_subscription->create()->load($subscription->getId());
                                $newSubscription->setStatusSuccess($sub_payment_status);
                                $newSubscription->setTxnId($txn_id);
                                $newSubscription->setPaypalId($paypal_result_id);
                                $newSubscription->setUpdateTime($date);
                                $newSubscription->save();

                                $this->UpdateChildSubscription($newSubscription,$sub_payment_status,0);
                            }
                        }
                        else{
                            $recurringExecuted = 1;
                            $this->createNewSubscription($sub_payment_status, $paypal_result_id,$checkout_ins_id,$txn_id);
                        }
                    }

                    if(isset($this->_postData['initial_payment_amount']) && ($this->_postData['initial_payment_amount'] > 0))
                    {
                        if($txn_type == "recurring_payment" && $this->_postData['payment_status'] == "Completed" && !$recurringExecuted)
                        {
                            $this->createNewSubscription($sub_payment_status, $paypal_result_id,$checkout_ins_id,$txn_id);  
                        }
                    }
                }
                else if (($order && $txn_type == "subscr_cancel") || ($order && $txn_type == "subscr_eot") ) {
                    // For cancelling subscription - unsubscribe event
                    $subscriptionCollection = $this->_subscriptionCollection->create()
                            ->addFieldToFilter("increment_id", $order->getIncrementId())
                            ->addFieldToFilter("customer_id", $order->getCustomerId());

                    $subscriptionCollection->getSelect()->order('subscription_history_id desc')->limit(1);

                    foreach ($subscriptionCollection as $subscription) {
                        $unSubscribeSubscription = $this->_subscription->create()->load($subscription->getId());
                        $unSubscribeSubscription->setStatusSuccess("Unsubscribed");
                        $unSubscribeSubscription->setUpdateTime($date);
                        $unSubscribeSubscription->save();

                        $this->UpdateChildSubscription($unSubscribeSubscription,"Unsubscribed",1);

                    }
                } else if ( ($txn_type == "recurring_payment_profile_cancel") && $order->getStatus() != "payment_completed") {
                   
                    $subscriptionCollection = $this->_subscriptionCollection->create()
                            ->addFieldToFilter("increment_id", $order->getIncrementId())
                            ->addFieldToFilter("customer_id", $order->getCustomerId());

                    $subscriptionCollection->getSelect()->order('subscription_history_id desc')->limit(1);

                    foreach ($subscriptionCollection as $subscription) {
                        $unSubscribeSubscription = $this->_subscription->create()->load($subscription->getId());
                        $unSubscribeSubscription->setStatusSuccess("Cancelled");
                        $unSubscribeSubscription->setDownloadLimit('0');
                        $unSubscribeSubscription->setUpdateTime($date);
                        $unSubscribeSubscription->save();

                        $this->UpdateChildSubscription($unSubscribeSubscription,"Cancelled",1);

                    }

                } else if ( $txn_type == "recurring_payment_profile_cancel" || $txn_type == "recurring_payment_expired" || $txn_type == "recurring_payment_suspended") {
                    // recurring_payment_profile_cancel or recurring_payment_suspended(this can be resumed also) case
                    // For cancelling subscription - unsubscribe event
                    // recurring_payment_expired case
                    // For subscription expiretion (specified billing cycles completed - this will not happen our cases) event
                    
                    $sub_payment_status = "Unsubscribed";
                    $this->updateSubHistoryRow($date,$sub_payment_status);

                }else if ( $txn_type == "recurring_payment_failed" || $txn_type == "recurring_payment_suspended_due_to_max_failed_payment") {
                    // recurring_payment_failed case
                    // For cancelling subscription - unsubscribe event
                    // recurring_payment_expired case
                    // For subscription expiretion (specified billing cycles completed - this will not happen our cases) event
                    $sub_payment_status = "Failed";
                    $this->updateSubHistoryRow($date,$sub_payment_status);
                }
                
            }
            elseif (!empty($checkout_ins_id) && empty($paypal_result_id)) {

                // Subscription Paid by 2Checkout  
                $this->_paymentMethod = "tco";
                $sub_payment_status = $this->getPaymentStatusTco();

                // For new subscription ,Need to implement old savaSubscription here
                $this->createNewSubscription($sub_payment_status,$paypal_result_id,$checkout_ins_id);
            }
            elseif (empty($checkout_ins_id) && empty($paypal_result_id) ) {
                // This for admin side subscription
                $this->_paymentMethod = "admin_subscription";
            }
            else{
                throw new \Exception('Unknown payment method.');
            }           
        } catch (Exception $e) {
            
            $this->_logger->debug(var_export($e->getMessage(), true));
            throw $e;
        }
    } 

    public function UpdateChildSubscription($subscription_history_detail,$status,$is_remove)
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
                $subscription_history_detail = $this->_subscriptionModel->getLastPaidSubscriptionhistory($customer_id);

                if($subscription_history_detail == "Unsubscribed" && $status == "Unsubscribed") :
                    continue;
                endif;

                if($status == "Cancelled") 
                {
                    $subscription_history_detail->setData("download_limit", 0);    
                }
                $subscription_history_detail->setData("status_success", $status);
                $subscription_history_detail->setData("update_time", $date);
                $subscription_history_detail->save();
            }
        }
        
    }   


    /**
     * This method is used for newer recurring mechanism (Magento 2.2) only
     * @param  string $update_time    
     * @param  string $status_success 
     * @return boolean whether row is updated or not
     */
    protected function updateSubHistoryRow($update_time , $status_success)
    {
        $updated = false;        
        $increment_id = $this->getIncrementIdOfRecurringOrder();
        $recurring_mapper_obj = $this->getPpRecurringMapperRow();
        
        if(!empty($increment_id) && is_object($recurring_mapper_obj)){
            $this->_subscriptionOrder = $this->_orderFactory->create()->loadByAttribute("increment_id", $increment_id);
            if($recurring_mapper_obj->getCustomerId() != $this->_subscriptionOrder->getCustomerId() ){
                throw new \Exception('Customer id mismatch in order invoice.');        
            }            

            $alreadyPaidSubscription = $this->_subscriptionCollection->create()
                                        ->addFieldToFilter("increment_id", $increment_id)
                                        ->addFieldToFilter(["parent_customer_id","parent_customer_id"],[['null'=>true],['eq'=>0]]);
            $alreadyPaidSubscription->getSelect()->order('subscription_history_id desc')->limit(1);
            
            // Recurring Payment
            if (is_object($alreadyPaidSubscription) && count($alreadyPaidSubscription) > 0 ) {
                foreach ($alreadyPaidSubscription as $subscription) {
                    
                    $unSubscribeSubscription = $this->_subscription->create()->load($subscription->getId());
                    $unSubscribeSubscription->setStatusSuccess($status_success);
                    $unSubscribeSubscription->setUpdateTime($update_time);
                    $unSubscribeSubscription->save();

                    $this->UpdateChildSubscription($unSubscribeSubscription,$status_success,1);

                    $updated = true;
                }
            }
        }
       
        return $updated;
    }

    protected function removeCustomer($customerid)
    {       
        $deletedCustomer_model = $this->_deletedcustomerbkp->create()->getCollection();
        $deletedCustomer_model->addFieldToFilter("customer_id",array("eq"=>$customerid));
        //$deletedCustomer_model->addFieldToFilter("is_remove_db",array("eq"=>1));
        
        $this->_registry->register('isSecureArea', true);
        foreach($deletedCustomer_model as $deleteCustomer)
        {
            $customer = $this->customerFactory->load($customerid);
            $customer->delete();
        }
    }

    
    /**
     * Add new subscription_history row for new subscription
     * @todo  need to refactor logic of saving subscription_period as now we are using recurring profile
     * @param  string $payment_status   
     * @param  string|null $paypal_result_id
     * @param  string|null $checkout_ins_id
     * @param  string|null $txn_id
     * @return void
     */
    protected function createNewSubscription($payment_status, $paypal_result_id, $checkout_ins_id, $txn_id = NULL){
        $new_subscription_saved = false;
        $order = $this->getSubscriptionOrder();
        $customerIp = $order->getRemoteIp();
        $sub_from_date = $this->_dateFactory->create()->gmtDate('Y-m-d');
        $subscription_order_start_date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
        $customer_id = $order->getCustomerId();
        $sub_end_date = "";
        $sub_period_text = "";
        $download_limit = "";
        $increment_id = $order->getIncrementId();

        try {
            // For m1 payapl and for m1 & m2 2checkout cases
            if(isset($this->_postData['invoice']) || $this->_paymentMethod == "tco"){
                foreach($order->getAllItems() as $item){

                    $product_type = $item->getProductType();
                    
                    if($product_type != "simple" ){

                        continue;
                    }
                }
                $skuSimpleProduct = $item->getProduct()->getSku();
                $download_limit = $item->getProduct()->getAttributeText('download_limit');
                $sub_period_lable = $item->getProduct()->getAttributeText('subscription_period');   //4 user enterprise license
                $sub_period_attribute = $item->getProduct()->getResource()->getAttribute('subscription_period');
                
                if ($sub_period_attribute->usesSource()) {
                    $option_id = $sub_period_attribute->getSource()->getOptionId($sub_period_lable);
                    $sub_period_text = $sub_period_attribute->setStoreId(0)->getSource()->getOptionText($option_id);//E.g : 12 month ( Enterprise )
                }
                
                $sub_end_date = $this->getSubEndDate($sub_period_text);
                // $sub_end_date = $this->getSubEndDate($sub_period);
            }
            //only for new payapl 
            elseif(isset($this->_postData['rp_invoice_id'])){                
                
                foreach($order->getAllItems() as $item){
                    $product_type = $item->getProductType();
                    $is_virtual_product = $item->getIsVirtual();

                    
                    // Throw exception if not a virtual product
                    if($product_type != "virtual" || $is_virtual_product != "1"){
                        throw new \Exception('Not a virtual Product.Id : '.$item->getProductId());
                    }

                    $skuSimpleProduct = $item->getProduct()->getSku();
                    $sub_period_lable = $item->getProduct()->getName();   //4 user enterprise license
                    $download_limit = $item->getProduct()->getAttributeText('download_limit');
                    $sub_period_lable = $item->getProduct()->getAttributeText('subscription_period');   //4 user enterprise license
                                        
                    $sub_end_date = $this->getSubEndDate_recurring($item);
                    
                }

            }

            if($sub_end_date == ""){
                throw new \Exception("Subscription End Date is not set", 1);
            }

            $newSubscription = $this->_subscription->create();
            $newSubscription->setSubscriptionPeriod($sub_period_lable);
            $newSubscription->setCustomerId($customer_id);
            $newSubscription->setFromDate($sub_from_date);
            $newSubscription->setSubscriptionStartDate($subscription_order_start_date);
            $newSubscription->setToDate($sub_end_date);
            $newSubscription->setRenewDate($sub_end_date);
            $newSubscription->setPaypalId($paypal_result_id);
            $newSubscription->setTxnId($txn_id);
            $newSubscription->setData("two_checkout_message_id", $checkout_ins_id);
            $newSubscription->setStatusSuccess($payment_status);
            $newSubscription->setDownloadLimit($download_limit);
            $newSubscription->setIncrementId($increment_id);
            $newSubscription->save();  

            $team_plan_array = $this->_teamplanModel->getTeamSubscriptionPlans();

            if(in_array($sub_period_lable,$team_plan_array))
            {
                $this->_teamplanModel->addChildSubscription($sub_period_lable,$customer_id,$sub_from_date,$subscription_order_start_date,$sub_end_date,$payment_status,$increment_id,$download_limit);
            }

            $period_full=$sub_period_lable;
            // Sub reminder success will not work in case of recurring_payment
            // $newSubscription->setReminderPurchase($order->getSubReminderSuccess());

            //Google Analytics snippet for Recurring Transactions Start
              $gtm_status=$this->scopeConfig->getValue('button/gtm_config/field1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //if(0):
            if($gtm_status):
                $ga_id = $this->scopeConfig->getValue('button/gtm_config/ga_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $connection = $this->_resourceConnection->getConnection();

                $subscription_results = $connection->fetchCol("SELECT `subscription_history_id` FROM `subscription_history` where `increment_id` =" . $increment_id);
                if (count($subscription_results) > 1) {
                    $documentHost = "Offline";
                } else {
                    $documentHost = "www.slideteam.net";
                }

                
                $dateToday = $this->date->gmtDate();
                //First Request for Transaction Start
                
                if(isset($this->_postData['invoice_list_amount']))
                {
                    $grandTotal = $this->_postData['invoice_list_amount'];    
                }
                elseif (isset($this->_postData['amount']) && ($this->_postData['amount'] > 0)) {
                    $grandTotal = $this->_postData['amount'];
                }
                elseif (isset($this->_postData['initial_payment_amount']) && ($this->_postData['initial_payment_amount'] > 0)) {
                    $grandTotal = $this->_postData['initial_payment_amount'];
                }
                else{
                    $grandTotal = $order->getGrandTotal();
                }


                $result=$connection->query("SELECT entity_id FROM `sales_order` WHERE `increment_id` = ".$order->getIncrementId());
                $row = $result->fetch();
                $orderId = $row['entity_id'];
                //$customerData = $this->customerRepository->getById($customer_id);
                $customerData = $this->customerFactory->load($customer_id);
                $cid = $customerData->getCid();
                if(!isset($cid) || $cid=="" )
                {
                 $cid = $customerData->getUuid();
                    if(!isset($cid) || $cid=="" )
                    {
                      $cid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                      mt_rand( 0, 0xffff ),
                      mt_rand( 0, 0x0fff ) | 0x4000,
                      mt_rand( 0, 0x3fff ) | 0x8000,
                      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
                      );
                      $customerGetDataModel = $customerData->getDataModel();
                      $customerData->setUuid($cid);
                      $customerData->updateData($customerGetDataModel);
                      $customerData->save();
                    }
                }
                $customerEmail = $customerData->getEmail();
                $customerEmailParts = explode("@",$customerEmail);
                $emailHost = $customerEmailParts[1];
                $customerCreatedDate = date('Y-m-d',strtotime($customerData->getCreatedAt()));
             
                $url = 'http://www.google-analytics.com/collect';
                
                
                $fields = array('v' => '1', 'tid' => $ga_id, 'cid' => $cid, 'uid' => $customer_id, 'uip' => $customerIp, 'dh' => $documentHost, 't' => 'transaction', 'ti' => $orderId, 'ta' => $emailHost, 'tr' => $grandTotal, 'ts' => '0', 'cd2' => $customer_id, 'cd5' => $period_full,'cd9' => $customerCreatedDate);

                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setHeaders(['Accept: application/json']);
                $client->setParameterPost($fields);
                $client->setMethod(\Zend_Http_Client::POST);
                   
                try {
                    $responseBody = $client->request()->getBody();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                
                $fields_second = array('v' => '1', 'tid' => $ga_id, 'cid' => $cid, 'uid' => $customer_id, 'uip' => $customerIp, 'dh' => $documentHost, 't' => 'item', 'ti' => $orderId, 'in' => $period_full, 'ic' => $skuSimpleProduct, 'iv' => 'Subscription', 'iq' => '1', 'ip' => $grandTotal, 'cd2' => $customer_id, 'cd5' => $period_full,'cd9' => $customerCreatedDate);


                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setHeaders(['Accept: application/json']);
                $client->setParameterPost($fields_second);
                $client->setMethod(\Zend_Http_Client::POST);
                   

                try {
                    $responseBody = $client->request()->getBody();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                
                //Google Analytics snippet for Recurring Transactions End
                endif;

               //Emarsys integration start//
                $field4 =  $this->EmarsysHelper->isApiEnabled();
        
                if($field4)
                {
                    switch($period_full)
                    {
                      case($period_full=="Monthly") : $subscription_duration = 1;break;
                      case($period_full=="Semi-annual") : $subscription_duration = 2;break;
                      case($period_full=="Annual") : $subscription_duration = 3;break;
                      case($period_full=="Annual + Custom Design") : $subscription_duration = 4;break;
                      case((stripos($period_full,'enterprise') !== false) || (stripos($period_full,'Team') !== false)) : $subscription_duration = 5;break;
                      case((stripos($period_full,'Monthly +') !== false)) : $subscription_duration = 6;break;
                      case((stripos($period_full,'Semi-annual +') !== false)) : $subscription_duration = 7;break;
                      case($period_full=="Annual 4 User License") : $subscription_duration = 11;break;
                      case($period_full=="Annual 20 User License") : $subscription_duration = 12;break;
                      case($period_full=="Annual Company Wide Unlimited User License") : $subscription_duration = 13;break;
                      case($period_full=="Annual 15 User Education License") : $subscription_duration = 14;break;
                      case($period_full=="Annual UNLIMITED User Institute Wide License") : $subscription_duration = 15;break;
                    }
                    $customerData = $this->customerRepository->getById($customer_id);
                    
                    $to_date=date("Y-m-d",strtotime($sub_end_date));

                    $contact = array(
                                "1"=>$customerData->getFirstname(),
                                "2"=>$customerData->getLastname(),
                                "3"=>$customerData->getEmail(),
                                "485"=>$customer_id,
                                "488"=>$to_date,
                                "489"=>$subscription_duration,
                                "490"=>1
                              );
                    $encode = json_encode($contact); 
                                
                    

                    try {
                        $apiHelper = $this->emarsysApiHelper;
                        $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
                //Emarsys integration end//

                //Zoho CRM integration start//
                if($this->zohoCRMHelper->isEnabled()){
                    $period_Name=$this->zohoCRMHelper->compareSubscription($period_full);
                    $subscriptionData = array(  
                        "First_Name"=>$customerData->getFirstname(),
                        "Last_Name"=>$customerData->getLastname(),
                        "Email"=>$customerData->getEmail(), 
                        "Priority"=>"3",
                        "Comment"=> "Edit Customer Subscription",
                        "Customer_Type"=>"Subscribed",
                        "Subscription_Type"=>$period_Name,
                        "Subscription_Start_Date"=>date("Y-m-d",strtotime($sub_from_date)),
                        "Subscription_End_Date"=>date("Y-m-d",strtotime($sub_end_date))
                    );
                    $this->zohoCRMHelper->editCustomer($subscriptionData,$customer_id);
                }
                //Zoho CRM integration end//

            $new_subscription_saved = true;        

        } catch (Exception $e) {
            $this->_logger->error(var_export($e->getMessage(), true));
            throw $e;
        }
        
    }


    /**
     * Calculate subscription end date from $sub_period_text 
     * @param  string $sub_period_text subscription_period attribute text
     * @return subscription_end_date 
     */
    protected function getSubEndDate($sub_period_text){
        //E.g : $sub_period_text = 12 month ( Enterprise )
        $period = explode(" ",$sub_period_text); //array("12","month","(","Enterprise",")")
        $period_no = $period[0];        // Number of months or days || 12
        $duration_text = $period[1];    // month

        $current_date = $this->_dateFactory->create()->gmtDate('Y-m-d');
        $sub_end_date = "";

        // here date() returns gmt timestamp so we used it
        if ($duration_text == "day"){
            // $sub_end_date = date('Y-m-d', strtotime(date("Y-m-d", strtotime($current_date)) . "+" . $period_no . " day"));
            $current_date_time_stamp = date("Y-m-d", strtotime($current_date));
            $time_stamp_end_date = strtotime($current_date_time_stamp . "+" . $period_no . " day");
            $sub_end_date = date('Y-m-d',$time_stamp_end_date);
        }
        else{
            // $sub_end_date = date('Y-m-d', strtotime(date("Y-m-d", strtotime($current_date)) . "+" . $period_no . " month"));
            $current_date_time_stamp = date("Y-m-d", strtotime($current_date));
            $time_stamp_end_date = strtotime($current_date_time_stamp . "+" . $period_no . " month");
            $sub_end_date = date('Y-m-d',$time_stamp_end_date);
        }
        return $sub_end_date;
    }


    protected function getSubEndDate_recurring($item){
        
        $product_recurring_profile = $item->getProduct()->getData('recurring_payment');
        $period_no = $product_recurring_profile['period_frequency'];        // Number of months or days || 12
        $duration_text = $product_recurring_profile['period_unit'];    // month
        // $period_no = "1";        
        // $duration_text = "day";    
        $current_date = $this->_dateFactory->create()->gmtDate('Y-m-d');
        $sub_end_date = "";
        
        // here date() returns gmt timestamp so we used it
        if ($duration_text == "day"){
            // $sub_end_date = date('Y-m-d', strtotime(date("Y-m-d", strtotime($current_date)) . "+" . $period_no . " day"));
            $current_date_time_stamp = date("Y-m-d", strtotime($current_date));
            $time_stamp_end_date = strtotime($current_date_time_stamp . "+" . $period_no . " day");
            $sub_end_date = date('Y-m-d',$time_stamp_end_date);
        }
        else{
            // $sub_end_date = date('Y-m-d', strtotime(date("Y-m-d", strtotime($current_date)) . "+" . $period_no . " month"));
            $current_date_time_stamp = date("Y-m-d", strtotime($current_date));
            $time_stamp_end_date = strtotime($current_date_time_stamp . "+" . $period_no . " month");
            $sub_end_date = date('Y-m-d',$time_stamp_end_date);
        }
        
        return $sub_end_date;
    }


    /**
     * Map payment status from Paypal ipn to subscription_history payment_status
     * 
     * @param  string $payment_status ipn/ins payment_status
     * @return string subscription_history payment_status
     */
    protected function getPaymentStatusPaypal(){
        $payment_status = $this->getRequestPostData('payment_status');
        $profile_status = $this->getRequestPostData('profile_status');
        $sub_payment_status = $payment_status;
        if(!empty($payment_status)){   
            switch ($payment_status) {
                case 'Completed':
                    $sub_payment_status = 'Paid';
                    break;
                case 'Processing':
                    $sub_payment_status = 'Processing';
                    break;
                case 'Denied':
                case 'Expired':
                case 'Failed':
                case 'Voided':
                    $sub_payment_status = 'Failed';
                    break;
                default:
                    $sub_payment_status = 'Failed';
                    break;
            }
        }
        elseif (!empty($profile_status)) {
            switch ($profile_status) {
                case 'Active':
                    $sub_payment_status = 'Paid';
                    break;
                case 'Pending':
                    $sub_payment_status = 'Processing';
                    break;
                case 'Suspended':
                case 'Cancelled':
                case 'Expired':
                    $sub_payment_status = 'Unsubscribed';
                    break;
                default:
                    $sub_payment_status = 'Failed';
                    break;
            }
        }
        return $sub_payment_status;
    }


    /**
     * Map payment status from 2Checkout INS to subscription_history payment_status
     * @return string subscription's payment status
     */
    protected function getPaymentStatusTco(){
        $sub_payment_status = "Processing";
        $messageType = $this->getRequestPostData("message_type");
        $fraudStatus = $this->getRequestPostData("fraud_status");

        if($messageType == \Tatva\TcoCheckout\Model\Notification::FRAUD_STATUS_CHANGED ){
            if($fraudStatus == 'fail'){
                $sub_payment_status = "Failed";
            }
            elseif ($fraudStatus == 'pass' || $fraudStatus == 'wait') {
                $sub_payment_status = "Paid";
            }
        }

        $messageTypeShouldHavePaymentCompleted = [  \Tatva\TcoCheckout\Model\Notification::ORDER_CREATED,
                                                    \Tatva\TcoCheckout\Model\Notification::INVOICE_STATUS_CHANGED,
                                                    \Tatva\TcoCheckout\Model\Notification::RECURRING_INSTALLMENT_SUCCESS
                                                    ];

        if(in_array($messageType, $messageTypeShouldHavePaymentCompleted)){
            $sub_payment_status = "Paid";
        }
        return $sub_payment_status;
    }


    /**
     * Get order object from INS/IPN 's "invoice" response param.
     * @return \Magento\Sales\Model\OrderFactory
     * @throws \Exception if IPN/INS has null increment_id/ivoice id for order
     */
    protected function getSubscriptionOrder(){
        try {
            if($this->_paymentMethod == "paypal"){
                $increment_id = null;

                if (!empty($this->getRequestPostData('invoice')) ) {
                    $increment_id = $this->getRequestPostData('invoice');
                    return $this->_subscriptionOrder = $this->_orderFactory->create()->loadByAttribute("increment_id", $increment_id);
                }
                elseif (!empty($this->getRequestPostData('recurring_payment_id')) ) {
                    
                    $increment_id = $this->getIncrementIdOfRecurringOrder();
                    $recurring_mapper_obj = $this->getPpRecurringMapperRow();
                    if(!empty($increment_id) && is_object($recurring_mapper_obj)){
                        $this->_subscriptionOrder = $this->_orderFactory->create()->loadByAttribute("increment_id", $increment_id);
                        if($recurring_mapper_obj->getCustomerId() != $this->_subscriptionOrder->getCustomerId() ){
                            throw new \Exception('Customer id mismatch in order invoice.');        
                        }
                        return $this->_subscriptionOrder;
                    }
                    else{
                        throw new \Exception('IPN/INS response has no recurring profile id (recurring_payment_id) from Paypal.');    
                    }
                }
                else{
                    throw new \Exception('IPN/INS response has no increment_id/invoice or recurring profiles\'s recurring_payment_id from Paypal.');
                }
            }
            elseif ($this->_paymentMethod == "tco") {
                if (!empty($this->getRequestPostData('vendor_order_id')) ) {
                    return $this->_subscriptionOrder = $this->_orderFactory->create()->loadByAttribute("increment_id", $this->getRequestPostData('vendor_order_id'));
                }
                else{
                    throw new \Exception('IPN/INS response has no increment_id/invoice from Two Checkout.');
                }
            }           
        } catch (Exception $e) {
            $this->_logger->debug(var_export($e->getMessage(), true));
            throw $e;
        }
    }


    protected function getPpRecurringMapperRow()
    {
        $recurring_mapper_obj = null;
        $ppRecurringMapperObj =  $this->_ppRecurringMapperFactory->create();
        $ppRecurringMapperCollection = $ppRecurringMapperObj->getCollection()
            ->addFieldToFilter('rp_profile_id',$this->getRequestPostData('recurring_payment_id'));
        $ppRecurringMapperCollection->getSelect()->order('map_id desc')->limit(1);
        
        if($ppRecurringMapperCollection->getSize()){
            foreach ($ppRecurringMapperCollection as $item) {
                $recurring_mapper_obj = $item;
            }
        }

        return $recurring_mapper_obj;
    }

    protected function getIncrementIdOfRecurringOrder()
    {
        $increment_id = null;
        $recurring_mapper_obj = $this->getPpRecurringMapperRow();
        if(is_object($recurring_mapper_obj)){
            $increment_id = $recurring_mapper_obj->getInvoice();
        }

        return $increment_id;
    }

}

?>