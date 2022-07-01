<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Controller\Stripe;

use Amasty\RecurringStripe\Api\IpnInterface;
use Amasty\RecurringStripe\Model\Adapter;
use Amasty\RecurringStripe\Model\ConfigWebhook;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Index extends Action
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var ConfigWebhook
     */
    private $configWebhook;

    /**
     * @var IpnInterface
     */
    private $stripeIpn;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;

    protected $scopeConfig;
    protected $_resourceConnection;
    protected $date;
    protected $customerFactory;
    protected $EmarsysHelper;
    protected $emarsysApiHelper;
    protected $orderRepo;
    protected $_httpClientFactory;
    protected $customerRepository;
    protected $subscriptionRepository;
    protected $subscriptionCancelOperation;
    protected $_recurringpayment;

    public function __construct(
        Context $context,
        FormKey $formKey,
        Adapter $adapter,
        ConfigWebhook $configWebhook,
        IpnInterface $stripeIpn,
        LoggerInterface $logger,
        \Tatva\Subscription\Model\SubscriptionFactory $subscription,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Tatva\Subscription\Helper\Data $SubscriptionHelper,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
        \Magento\Sales\Api\Data\OrderInterface $orderRepo,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        CustomerRepositoryInterface $customerRepository,
        \Amasty\RecurringPayments\Api\Subscription\RepositoryInterface $subscriptionRepository,
        \Amasty\RecurringPayments\Model\Subscription\Operation\SubscriptionCancelOperation $subscriptionCancelOperation,
        \Tatva\RecurringOrders\Model\Recurringpayment $recurringpayment,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductRepository $productRepo,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Store\Model\StoreManagerInterface $stroeInterface,
        \Tatva\Subscription\Helper\TeamPlans $teamplanModel
    ) {
        parent::__construct($context);
        $this->setFormKey($formKey);

        $this->adapter = $adapter;
        $this->configWebhook = $configWebhook;
        $this->stripeIpn = $stripeIpn;
        $this->logger = $logger;
        $this->_subscription = $subscription;
        $this->scopeConfig = $scopeConfig;
        $this->_resourceConnection = $resourceConnection;
        $this->date = $date;
        $this->customerFactory = $customerFactory;
        $this->SubscriptionHelper = $SubscriptionHelper;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
        $this->orderRepo = $orderRepo;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->customerRepository = $customerRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionCancelOperation = $subscriptionCancelOperation;
        $this->zohoCRMHelper = $zohoCRMHelper;
        $this->_recurringpayment = $recurringpayment;
        //$this->_objectManager = $objectmanager;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->mathRandom = $mathRandom;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->transportBuilder = $transportBuilder;
        $this->_orderFactory = $orderFactory;
        $this->_productRepo = $productRepo;
        $this->_priceHelper = $priceHelper;
        $this->_stroeInterface = $stroeInterface;
        $this->_teamplanModel = $teamplanModel;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        $payload = $request->getContent();
        $sigHeader = $request->getServer('HTTP_STRIPE_SIGNATURE');
        $event = null;

        try {
            /** @var \Stripe\Event $event */
            $event = $this->adapter->eventRetrieve(
                (string)$payload,
                (string)$sigHeader,
                $this->configWebhook->getWebhookSecret()
            );

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-debug/stripe-test-log.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);   
            $logger->info($event); 
            
            if($event->type == "invoice.payment_succeeded" && $event->data->object->status == "paid")
            {                
                $payment_status = $event->data->object->status;         
                $subscriptionid = $event->data->object->subscription;           
                $subscription_order_start_date = $event->created;           
                $increment_id = $event->data->object->lines->data[0]->metadata->increment_id;               
                $sub_from_date = date("Y-m-d",$event->data->object->lines->data[0]->period->start);             
                $sub_end_date = date("Y-m-d",$event->data->object->lines->data[0]->period->end);   
                
                if(!empty($increment_id))
                {
                    
                    $order = $this->orderRepo->loadByIncrementId($increment_id);
                    $orderId = $order->getId();

                    $this->_recurringpayment->Recurringpayment($orderId);
                }
                else
                {
                    $increment_id = $orderId = NULL;
                }

                $logger->info("here 1");
                
                $subHistory = $this->_subscription->create()->getCollection();
                $dateToday = $this->date->gmtDate();
                
                //$subHistory->addFieldToFilter("stripe_checkout_message_id",array('eq'=>$subscriptionid))->addFieldToFilter('to_date',array("lt"=>$sub_end_date))->setOrder('subscription_history_id','desc'); 

                $subHistory->addFieldToFilter("stripe_checkout_message_id",array('eq'=>$subscriptionid))->setOrder('subscription_history_id','desc'); 
                
                if($subHistory->getSize())
                { 
                    $subscription = $subHistory->getFirstItem();
                    $sub_period_lable = $subscription->getSubscriptionPeriod();                    
                    $customer_id = $subscription->getCustomerId();                    
                    $download_limit = $subscription->getDownloadLimit();
                    $documentHost = "offline";

                    $logger->info("here 2");

                    if (empty($increment_id)) {
                        $increment_id = $subscription->getIncrementId();
                        $order = $this->orderRepo->loadByIncrementId($increment_id);
                        $documentHost = "invoice-recurring-stripe";

                        $logger->info("here 3");
                    }                    

                    $newSubscription = $this->_subscription->create()->getCollection()->addFieldToFilter('from_date',array("eq"=>$sub_from_date))->addFieldToFilter("to_date",array("eq"=>$sub_end_date))->addFieldToFilter("customer_id",array("eq"=>$customer_id))->addFieldToFilter("increment_id",array("eq"=>$increment_id));
                    if($newSubscription->getSize())
                    {
                        $logger->info("here 4");
                        return;
                    }
                   
                    $newSubscription = $this->_subscription->create();
                    $newSubscription->setFromDate($sub_from_date)->setSubscriptionStartDate($subscription_order_start_date)
                    ->setToDate($sub_end_date)->setRenewDate($sub_end_date)
                    ->setStatusSuccess($payment_status)->setStripeCheckoutMessageId($subscriptionid)->setIncrementId($increment_id)
                    ->setSubscriptionPeriod($sub_period_lable)->setCustomerId($customer_id)->setDownloadLimit($download_limit)->save();

                    $logger->info("here 5");

                    /*Child subscription update*/

                    $team_plan_array = $this->_teamplanModel->getTeamSubscriptionPlans();
                    if(in_array($sub_period_lable,$team_plan_array))
                    {
                        $this->_teamplanModel->addChildSubscription($sub_period_lable,$customer_id,$sub_from_date,$subscription_order_start_date,$sub_end_date,$payment_status,$increment_id,$download_limit);
                    }

                    /*Child subscription update End*/
                    
                    $customerData = $this->customerFactory->create()->load($customer_id);
                    $this->changeSubscriptionTitle($event,$increment_id,$order,$documentHost);
                        
                    $this->sendDataToGA($sub_period_lable,$customerData,$increment_id,$order,$documentHost);

                    $this->sendDataToEmarsys($sub_period_lable,$sub_end_date,$customerData);

                    $this->sendDataToZoho($sub_from_date,$sub_end_date,$customerData);

                    return;
                }
                else
                {
                    $logger->info("else 3");
                    if(empty($increment_id))
                    {
                        $secretkey = $this->scopeConfig->getValue('payment/amasty_stripe/private_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $stripe = new \Stripe\StripeClient($secretkey);
                        $charge = $event->data->object->charge;

                        $logger->info("else 4");

                        $customerEmail = $event->data->object->customer_email;
                        $customer_exists = $this->emailExistOrNot($event);
                        if ($customer_exists == 1) {
                            $logger->info("else 5");
                            $this->createCustomer($event);
                        }

                        $logger->info("else 6");

                        $order = $this->createOrder($event);
                        $increment_id = $order->getIncrementId();
                        $orderId = $order->getId();
                        //$stripe->charges->update($charge,['metadata' => ['increment_id' => $increment_id,'order_id' => $orderId]]);

                        $logger->info("else 7");

                        $customerEmail = $event->data->object->customer_email;
                        $customer = $this->customerRepository->get($customerEmail);
                        $customer_id = $customer->getId();
                        $products = $this->getProductCollection($event);
                        $download_limit = $products->getAttributeText('download_limit');
                        $sub_period_lable = $products->getAttributeText('subscription_period');

                        $logger->info("else 8");

                        $newSubscription = $this->_subscription->create();
                        $newSubscription->setFromDate($sub_from_date)->setSubscriptionStartDate($subscription_order_start_date)
                        ->setToDate($sub_end_date)->setRenewDate($sub_end_date)
                        ->setStatusSuccess($payment_status)->setStripeCheckoutMessageId($subscriptionid)->setIncrementId($increment_id)
                        ->setAdminModified(0)->setSubscriptionPeriod($sub_period_lable)->setCustomerId($customer_id)->setDownloadLimit($download_limit)->save();    
                        
                        $team_plan_array = $this->_teamplanModel->getTeamSubscriptionPlans();
                        if(in_array($sub_period_lable,$team_plan_array))
                        {
                            $this->_teamplanModel->addChildSubscription($sub_period_lable,$customer_id,$sub_from_date,$subscription_order_start_date,$sub_end_date,$payment_status,$increment_id,$download_limit);
                        }

                        $logger->info("else 9");

                        if(strtolower($sub_period_lable) == strtolower("Monthly"))
                        {
                            $delivery = "Once a month";
                            $frequency = 1;
                            $frequency_unit = "month";                            
                        }
                        elseif (strtolower($sub_period_lable) == strtolower("Semi Annual"))
                        {
                            $delivery = "Every 6 months";
                            $frequency = 6;
                            $frequency_unit = "month";                            
                        }
                        elseif(strtolower($sub_period_lable) == strtolower("Daily"))
                        {
                            $delivery = "Once a day";
                            $frequency = 1;
                            $frequency_unit = "Day";
                        }
                        else
                        {
                            $delivery = "Every 12 months";
                            $frequency = 12;
                            $frequency_unit = "month";                            
                        }

                        $connection = $this->_resourceConnection->getConnection();
                        $data = array(
                            "created_at" => $subscription_order_start_date,
                            "subscription_id" => $subscriptionid,
                            "order_id" => $orderId,
                            "product_id" => $products->getId(),
                            "qty" => 1,
                            "customer_id" => $customer_id,
                            "payment_method" => "amasty_stripe",
                            "address_id" => NULL,
                            "store_id" => 1,
                            "shipping_method" => '',
                            "initial_fee" => 0,
                            "base_discount_amount" => 0,
                            "base_grand_total" => $order->getGrandTotal(),
                            "free_shipping" => 0,
                            "trial_days" => 0,
                            "delivery" => $delivery,
                            "remaining_discount_cycles" => NULL,
                            "status" => "active",
                            "frequency" => $frequency,
                            "frequency_unit" => $frequency_unit,
                            "last_payment_date" => NULL,
                            "start_date" => $sub_from_date,
                            "end_date" => NULL,
                            "count_discount_cycles" => 0,
                            "customer_timezone" => NULL,
                            "customer_email" => $customerEmail,
                            "base_grand_total_with_discount" => $order->getGrandTotal() 
                        );
                        $connection->insert("amasty_recurring_payments_subscription", $data);

                        $documentHost = "invoice-new-stripe";
                        $customerData = $this->customerFactory->create()->load($customer_id);

                        $this->changeSubscriptionTitle($event,$increment_id,$order,$documentHost);
                        
                        $this->sendDataToGA($sub_period_lable,$customerData,$increment_id,$order,$documentHost);

                        $this->sendDataToEmarsys($sub_period_lable,$sub_end_date,$customerData);

                        $this->sendDataToZoho($sub_from_date,$sub_end_date,$customerData);

                        return;
                    }
                }
            }
            elseif($event->type == "customer.subscription.deleted" && $event->data->object->status == "canceled")
            {
                $subscriptionid = $event->data->object->id;
                
                $subHistory = $this->_subscription->create()->getCollection();
                $subHistory->addFieldToFilter("stripe_checkout_message_id",array('eq'=>$subscriptionid))->setOrder('subscription_history_id','desc');
                
                if($subHistory->getSize())
                {
                    $subscription = $subHistory->getFirstItem();
                    $subModel = $this->_subscription->create()->load($subscription->getSubscriptionHistoryId());
                    $subModel->setStatusSuccess("Unsubscribed")->save();

                    $sub_period_lable = $subscription->getSubscriptionPeriod();
                    $team_plan_array = $this->_teamplanModel->getTeamSubscriptionPlans();
                    if(in_array($sub_period_lable,$team_plan_array))
                    {
                        $this->_teamplanModel->UnSubscribeChild($subModel,"Unsubscribed");    
                    }
                }
            }
            elseif($event->type == "charge.refunded" && $event->data->object->status == "succeeded")
            {
                $logger->info("Refund 1");
                $email = $event->data->object->receipt_email;
                $customer = $this->customerFactory->create()->setWebsiteId(1)->loadByEmail($email);
                $customerId = $customer->getId();
                $logger->info("Refund 2");
                if(empty($customerId))
                {
                    return;
                }
                $subHistory = $this->_subscription->create()->getCollection();
                $subHistory->addFieldToFilter("customer_id",array('eq'=>$customerId))->setOrder('subscription_history_id','desc');

                if($subHistory->getSize())
                {    
                    $logger->info("Refund 3");
                    $subscription = $subHistory->getFirstItem();
                    $stripe_checkout_message_id = $subscription->getStripeCheckoutMessageId();

                    $subscriptionModel = $this->subscriptionRepository->getBySubscriptionId($stripe_checkout_message_id);
                    $unsubscribed = $this->subscriptionCancelOperation->execute($subscriptionModel);

                    $subModel = $this->_subscription->create()->load($subscription->getSubscriptionHistoryId());
                    $subModel->setDownloadLimit(0)->setStatusSuccess("Unsubscribed")->save();

                    $sub_period_lable = $subscription->getSubscriptionPeriod();
                    $team_plan_array = $this->_teamplanModel->getTeamSubscriptionPlans();
                    if(in_array($sub_period_lable,$team_plan_array))
                    {
                        $this->_teamplanModel->UnSubscribeChild($subModel,"Unsubscribed");    
                    }

                    $logger->info("Refund 4");
                    $sub_period_lable = $subscription->getData('subscription_period');
                    $logger->info("Refund 5");                  
                    //$customer_id = $subscription->getData('customer_id');
                    //$logger->info($customer_id);
                    //$customerData = $this->customerFactory->create()->load($customer_id);
                    //$logger->info("Refund 6");
                    $increment_id = $subscription->getData('increment_id');                    
                    $logger->info("Refund 7");
                    $order = $this->orderRepo->loadByIncrementId($increment_id);
                    $logger->info("Refund 8");
                    $documentHost = "Refunds";
                    $logger->info("Refund 9");
                    $this->sendDataToGA($sub_period_lable,$customer,$increment_id,$order,$documentHost);
                }
                return;
            }
            
            $this->stripeIpn->processIpnRequest($event);
            $result->setHttpResponseCode(200);
        } catch (\Exception $e) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-error.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $this->logger->critical($e->getMessage());
            $logger->info($e->getMessage());
            $result->setHttpResponseCode(400);
        }
        $result->setContents(''); // Prevent fatal error on Magento 2.3.3

        return $result;
    }

    private function changeSubscriptionTitle($event,$increment_id,$order,$documentHost)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-debug/stripe-test-log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);   
        $logger->info("change Subscription Title 1");

        $payment_intent = $event->data->object->payment_intent;                
        $secretkey = $this->scopeConfig->getValue('payment/amasty_stripe/private_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $stripe = new \Stripe\StripeClient($secretkey);
        $customerEmail = $event->data->object->customer_email;

        $logger->info("change Subscription Title 2");
        
        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $orderItems = $order->getAllItems();
        $description = "Subscription Creation Recurring";
        $logger->info("change Subscription Title 3");
        foreach ($orderItems as $_item) {
            $productId = $_item->getProductId();
            //$productModel = $this->_productFactory;
            $subscriptionperiod = $this->_productRepo->getById($productId)->getAttributeText('subscription_period');
            $logger->info("change Subscription Title 4");

            if($_item->getProductType() == "virtual") {
                if($documentHost == "invoice-new-stripe")
                {
                    $description =  $customerEmail." - ".$increment_id." - ".$subscriptionperiod;
                    $logger->info("change Subscription Title 5");
                }
                else
                {
                    $description =  $customerEmail." - ".$increment_id." - ".$subscriptionperiod." - Recurring";
                    $logger->info("change Subscription Title 6");
                }
            }
        }
        $logger->info("change Subscription Title 7");
        $stripe->paymentIntents->update($payment_intent,['description'=> $description]);    
    }

    private function sendDataToGA($sub_period_lable,$customerData,$increment_id,$order,$documentHost)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-debug/stripe-test-log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);   
        $logger->info(" Send GA 1");

        $period_full = $sub_period_lable;
        $gtm_status=$this->scopeConfig->getValue('button/gtm_config/field1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $logger->info(" Send GA 2");
        if($gtm_status)
        {
            $logger->info(" Send GA 3");

            $ga_id = $this->scopeConfig->getValue('button/gtm_config/ga_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
            $connection = $this->_resourceConnection->getConnection();
            $subscription_results = $connection->fetchCol("SELECT `subscription_history_id` FROM `subscription_history` where `increment_id` =" . $increment_id);
            $dateToday = $this->date->gmtDate();
            $grandTotal = $order->getGrandTotal();
            $cid = $customerData->getCid();

            $logger->info(" Send GA 4");

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

            $logger->info(" Send GA 5");

            $customerEmail = $customerData->getEmail();
            $customerEmailParts = explode("@",$customerEmail);
            $emailHost = $customerEmailParts[1];
            $customerCreatedDate = date('Y-m-d',strtotime($customerData->getCreatedAt()));
            $customerIp = $order->getRemoteIp();
            $customer_id = $customerData->getId();
            $orderId = $order->getId();
            
            $logger->info(" Send GA 6");

            $url = 'http://www.google-analytics.com/collect';

            $fields = array('v' => '1', 'tid' => $ga_id, 'cid' => $cid, 'uid' => $customer_id, 'uip' => $customerIp, 'dh' => $documentHost, 't' => 'transaction', 'ti' => $orderId, 'ta' => $emailHost, 'tr' => $grandTotal, 'ts' => '0', 'cd2' => $customer_id, 'cd5' => $period_full, 'cd9' => $customerCreatedDate);

            $client = $this->_httpClientFactory->create();
            $client->setUri($url);
            $client->setHeaders(['Accept: application/json']);
            $client->setParameterPost($fields);
            $client->setMethod(\Zend_Http_Client::POST);

            $logger->info(" Send GA 7");
            try {
                $responseBody = $client->request()->getBody();
                $logger->info(" Send GA 8");
            } catch (\Exception $e) {
                $logger->info($e->getMessage());
                echo $e->getMessage();
            }

            $skuSimpleProduct = "";
            foreach($order->getAllItems() as $item){

                $product_type = $item->getProductType();
                $is_virtual_product = $item->getIsVirtual();
                
                if($product_type != "virtual" || $is_virtual_product != "1"){
                    throw new \Exception('Not a virtual Product.Id : '.$item->getProductId());
                }

                $skuSimpleProduct = $item->getProduct()->getSku();
            }

            $logger->info(" Send GA 9");
            $fields_second = array('v' => '1', 'tid' => $ga_id, 'cid' => $cid, 'uid' => $customer_id, 'uip' => $customerIp, 'dh' => $documentHost, 't' => 'item', 'ti' => $orderId, 'in' => $period_full, 'ic' => $skuSimpleProduct, 'iv' => 'Subscription', 'iq' => '1', 'ip' => $grandTotal, 'cd2' => $customer_id, 'cd5' => $period_full, 'cd9' => $customerCreatedDate);

            $client = $this->_httpClientFactory->create();
            $client->setUri($url);
            $client->setHeaders(['Accept: application/json']);
            $client->setParameterPost($fields_second);
            $client->setMethod(\Zend_Http_Client::POST);
            $logger->info(" Send GA 10");
            try {
                $responseBody = $client->request()->getBody();
                $logger->info(" Send GA 11");

            } catch (\Exception $e) {
                $logger->info($e->getMessage());
                echo $e->getMessage();
            }
        }
    }

    private function sendDataToEmarsys($sub_period_lable,$sub_end_date,$customerData)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-debug/stripe-test-log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);   
        $logger->info("Send Emarsys 1");

        $period_full = $sub_period_lable;
        $customer_id = $customerData->getId();

        $field4 =  $this->EmarsysHelper->isApiEnabled();
        if($field4)
        {
            $logger->info("Send Emarsys 2");
            switch($period_full)
            {
                case($period_full=="Daily") : $subscription_duration = 20;break;
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
            $logger->info("Send Emarsys 3");

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

            $logger->info("Send Emarsys 4");

            try {
                $apiHelper = $this->emarsysApiHelper;
                $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                $logger->info("Send Emarsys 5");

            } catch (\Exception $e) {
                $logger->info($e->getMessage());
                echo $e->getMessage();
            }
        }    
    }

    private function sendDataToZoho($sub_from_date,$sub_end_date,$customerData)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-debug/stripe-test-log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);   
        $logger->info(" Send Zoho 1");  

        if($this->zohoCRMHelper->isEnabled()){
            $customer_id = $customerData->getId();
            $logger->info(" Send Zoho 2");
            $subscriptionData = array(
                "Priority"=>"3",
                "Comment"=> "Update Customer Subscription",
                "Customer_Type"=>"Subscribed",
                "Subscription_Start_Date"=>date("Y-m-d",strtotime($sub_from_date)),
                "Subscription_End_Date"=>date("Y-m-d",strtotime($sub_end_date))
            );
            $this->zohoCRMHelper->editCustomer($subscriptionData,$customer_id);
        } 
    }

    private function createCustomer($event)
    {
        $customerEmail = $event->data->object->customer_email;
        $customerName = $event->data->object->customer_name;
        $password = $this->mathRandom->getRandomString(8, $chars=null);
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId(1)
            ->setFirstname($customerName)
            ->setLastname($customerName)
            ->setEmail($customerEmail)
            ->setPassword($password);
        $customer->save();
        
        $this->sendEmail($customerName,$customerEmail,$password);
        
        return $customer;
    }

    private function createOrder($event)
    {
        $products = $this->getProductCollection($event);
        
        $customerEmail = $event->data->object->customer_email;
        $customerName = $event->data->object->customer_name;
        $price = $event->data->object->lines->data[0]->amount;
        $price = $price/100;
        $formattedPrice = $this->_priceHelper->currency($price, false, false);
        
        $orderInfo =[
            'email'        => $customerEmail,
            'currency_id'  => 'USD',
            'address' =>[
                'firstname'    => $customerName,
                'lastname'     => $customerName
            ],
            'items'=>
                [
                    [
                        'product_id' => $products->getEntityId(),
                        'qty' => 1
                    ]
                ]
        ];

        $tempOrder=[     
            'billing_address' =>[
                'firstname'    => $customerName,
                'lastname'     => $customerName,
                'street' => "Sample Address",
                'city' => "Sample City",
                'country_id' => "US",
                'region' => "Sample State",
                'postcode' => "123456",
                'save_in_address_book' => 0
            ]
        ];

        $customer = $this->customerRepository->get($customerEmail);
        $customer_id = $customer->getId();
        if ($customer_id) {
          //Create Order
            $store = $this->_stroeInterface->getStore();
            $quote = $this->quote->create();
            $quote->setStore($store);
            $quote->setCurrency();
            $quote->assignCustomer($customer);
            //Add Items in Quote Object
            foreach($orderInfo['items'] as $item){
                //$ProductFactory = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory');

                $product= $this->_productRepo->getById($item['product_id']);
                $product->setPrice($formattedPrice);
                $quote->addProduct($product,intval($item['qty']));
            }
            $quote->getBillingAddress()->addData($tempOrder['billing_address']);
            $quote->getShippingAddress()->addData($tempOrder['billing_address']);
            $quote->setPaymentMethod('cashondelivery'); 
            $quote->setInventoryProcessed(false);
            $quote->getPayment()->addData(['method' => 'cashondelivery']);
            $quote->save();
            $quote->collectTotals()->save();
            
            $order = $this->quoteManagement->submit($quote);
            //$this->orderSender->send($order,false);
            
            $orderdata = $this->_orderFactory->create()->load($order->getId());
            $orderdata->setState("complete")->setStatus("complete");
            
            $orderdata->setSubtotal($price);
            $orderdata->setSubtotalInvoiced($price);
            $orderdata->setBaseSubtotal($price);
            $orderdata->setBaseSubtotalInvoiced($price);
            $orderdata->setBaseTotalInvoiced($price);
            $orderdata->setBaseTotalPaid($price);
            $orderdata->setGrandTotal($price);
            $orderdata->setBaseGrandTotal($price);
            $orderdata->setTotalInvoiced($price);
            $orderdata->setTotalPaid($price);
            $orderdata->setBaseSubtotalInclTax($price);
            $orderdata->setSubtotalInclTax($price);
            $orderdata->setBaseTaxAmount("0.00");
            $orderdata->setBaseTotalDue("0.00");
            $orderdata->setTotalDue("0.00");
            $orderdata->save();

            $data = [
                  "entity_id" => $order->getId(),
                  "status" => $orderdata->getStatus(),
                  "store_id" => $orderdata->getStoreId(),
                  "store_name" => $orderdata->getStoreName(),
                  "customer_id" => $orderdata->getCustomerId(),
                  "base_grand_total" => $orderdata->getBaseGrandTotal(),
                  "base_total_paid" => $orderdata->getBaseTotalPaid(),
                  "grand_total" => $orderdata->getGrandTotal(),
                  "total_paid" => $orderdata->getTotalPaid(),
                  "increment_id" => $orderdata->getIncrementId(),
                  "base_currency_code" => $orderdata->getBaseCurrencyCode(),
                  "order_currency_code" => $orderdata->getOrderCurrencyCode(),
                  "shipping_name" => $orderdata->getShippingName(),
                  "billing_name" => $orderdata->getBillingName(),
                  "created_at" => $orderdata->getCreatedAt(),
                  "updated_at" => $orderdata->getUpdatedAt(),
                  "billing_address" => NULL,
                  "shipping_address" => NULL,
                  "shipping_information" => $orderdata->getShippingInformation(),
                  "customer_email" => $orderdata->getCustomerEmail(),
                  "customer_group" => $orderdata->getCustomerGroup(),
                  "subtotal" => $orderdata->getSubtotal(),
                  "shipping_and_handling" => $orderdata->getShippingAndHandling(),
                  "customer_name" => $orderdata->getCustomerName(),
                  "payment_method" => $orderdata->getPaymentMethod(),
                  "total_refunded" => $orderdata->getTotalRefunded(),
                  "signifyd_guarantee_status" => $orderdata->getSignifydGuaranteeStatus(),
                  "pickup_location_code" => $orderdata->getPickupLocationCode(),  
            ];
            // Get Order Increment ID
            $tableName = "sales_order_grid";
            $connection = $this->_resourceConnection->getConnection();
            $connection->insert($tableName, $data);
            $orderId = $order->getIncrementId();  
            
            return $order;  
        }
    }

    public function getProductCollection($event)
    {
        $description = $event->data->object->lines->data[0]->description;
        $sku = explode("1 ",$description);
        $sku2 = explode("(",$sku[1]);
        $sku3 = trim($sku2[0]);
        $explode = explode(" ",$sku3);
        $finalskuarray = array();
        foreach($explode as $key => $part)
        {
            if($key == 0)
                continue;
            $finalskuarray[] = $part;
        }

        $finalsku = implode(" ",$finalskuarray);
        if($finalsku == "1month-")
        {
            $finalsku = "1month-1";
        }
        //$sku2 = explode("1 ×",$description);
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-debug/stripe-reccuring-order-backend-test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);   
        $logger->info("product sku before");
        $logger->info($finalsku);
        
        
        //$productCollection = $this->_objectManager->create('Magento\Catalog\Model\ProductRepository');
        $collection = $this->_productRepo->get($finalsku);
        $logger->info("product sku after");
        return $collection;
    }

    public function sendEmail($customerName,$customerEmail,$password)
    {
        $templateId = 'customer_create_stripeaccount_email_template';
        $fromEmail = 'support@slideteam.net';
        $fromName = 'Slideteam support';
        $toEmail = $customerEmail;
 
        try {
            $templateVars = [
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'password' => $password
            ];
 
            $storeId = 1;
 
            $from = ['email' => $fromEmail, 'name' => $fromName];
            
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            
            $this->logger->info($e->getMessage());
        }
    }

    public function emailExistOrNot($event): bool
    {
        $email = $event->data->object->customer_email;
        $websiteId = 1;
        $isEmailNotExists = $this->customerAccountManagement->isEmailAvailable($email, $websiteId);
        return $isEmailNotExists;
    }
    /**
     * @param FormKey $formKey
     */
    private function setFormKey(FormKey $formKey)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        if (empty($request->getParam('form_key'))) {
            $request->setParam('form_key', $formKey->getFormKey());
        }
    }
}
