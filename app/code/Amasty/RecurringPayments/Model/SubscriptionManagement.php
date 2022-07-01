<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Api\Subscription\AddressInterfaceFactory;
use Amasty\RecurringPayments\Api\Subscription\AddressRepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\GridInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface as SubscriptionRepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterfaceFactory;
use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Amasty\RecurringPayments\Model\Subscription\Mapper\BillingFrequencyLabelMapper;
use Amasty\RecurringPayments\Model\Subscription\Mapper\StartEndDateMapper;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SubscriptionManagement
{
    const TYPE_OPTIONS = [
        Type::TYPE_BUNDLE                   => ['bundle_option', 'bundle_option_qty'],
        DownloadableType::TYPE_DOWNLOADABLE => ['links'],
        Configurable::TYPE_CODE             => ['super_attribute']
    ];

    /**
     * @var GridInterface[]
     */
    private $subscriptionProcessors;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var SubscriptionInterfaceFactory
     */
    private $subscriptionFactory;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var StartEndDateMapper
     */
    private $startEndDateMapper;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var BillingFrequencyLabelMapper
     */
    private $billingFrequencyLabelMapper;

    /**
     * @var QuoteGenerator
     */
    private $quoteGenerator;

    /**
     * @var Config
     */
    private $config;
    
    protected $_subscription;
    protected $_dateFactory;
    protected $_product;
    protected $OrderRepo;
    protected $scopeConfig;
    protected $_resourceConnection;
    protected $date;
    protected $customerFactory;
    protected $EmarsysHelper;
    protected $emarsysApiHelper;
    protected $_httpClientFactory;
    protected $customerRepository;
    /**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;

    public function __construct(
        LoggerInterface $logger,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        SubscriptionInterfaceFactory $subscriptionFactory,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SerializerInterface $serializer,
        StartEndDateMapper $startEndDateMapper,
        ItemDataRetriever $itemDataRetriever,
        Amount $amount,
        BillingFrequencyLabelMapper $billingFrequencyLabelMapper,
        QuoteGenerator $quoteGenerator,
        Config $config,
        \Tatva\Subscription\Model\SubscriptionFactory $subscription,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Sales\Model\OrderFactory $orderRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        CustomerRepositoryInterface $customerRepository,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Tatva\Subscription\Helper\Data $SubscriptionHelper,
        \Tatva\Subscription\Helper\TeamPlans $teamplanModel,
        array $subscriptionProcessors = []
    ) {
        $this->subscriptionProcessors = $subscriptionProcessors;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->serializer = $serializer;
        $this->startEndDateMapper = $startEndDateMapper;
        $this->itemDataRetriever = $itemDataRetriever;
        $this->amount = $amount;
        $this->billingFrequencyLabelMapper = $billingFrequencyLabelMapper;
        $this->quoteGenerator = $quoteGenerator;
        $this->config = $config;
        
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->_subscription = $subscription;
        $this->_product = $product;
        $this->OrderRepo = $orderRepo;
        $this->scopeConfig = $scopeConfig;
        $this->_resourceConnection = $resourceConnection;
        $this->date = $date;
        $this->customerFactory = $customerFactory->create();
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->customerRepository = $customerRepository;
        $this->zohoCRMHelper = $zohoCRMHelper;
        $this->_teamplanModel = $teamplanModel;
        $this->SubscriptionHelper = $SubscriptionHelper;
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    public function getSubscriptions(int $customerId): array
    {
        $subscriptions = [];

        /** @var GridInterface $processor */
        foreach ($this->subscriptionProcessors as $processor) {
            try {
                $subscriptions[] = $processor->process($customerId);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }

        if (empty($subscriptions)) {
            return [];
        }

        $subscriptions = array_merge(...$subscriptions);

        usort(
            $subscriptions,
            function (SubscriptionInfoInterface $a, SubscriptionInfoInterface $b) {
                return -(strtotime($a->getStartDate()) <=> strtotime($b->getStartDate()));
            }
        );

        return $subscriptions;
    }

    /**
     * @param OrderInterface $order
     * @param Quote\Item\AbstractItem $item
     * @return SubscriptionInterface
     */
    public function generateSubscription(
        OrderInterface $order,
        Quote\Item\AbstractItem $item
    ): SubscriptionInterface {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $item->getQuote();
        $product = $item->getProduct();
        $subscriptionPlan = $this->itemDataRetriever->getPlan($item, false);

        /** @var SubscriptionInterface $subscription */
        $subscription = $this->subscriptionFactory->create();

        $trialDays = $subscriptionPlan->getEnableTrial()
            ? $subscriptionPlan->getTrialDays()
            : 0;
        $initialFee = 0;
        if ($subscriptionPlan->getEnableInitialFee() && $subscriptionPlan->getInitialFeeAmount()) {
            $initialFee = $this->amount->getAmount(
                $product,
                (float)$subscriptionPlan->getInitialFeeAmount(),
                $subscriptionPlan->getInitialFeeType(),
                $item
            );
        }

        list($startDate, $endDate) = $this->startEndDateMapper->getStartEndDate($item);
        $timezoneName = $this->startEndDateMapper->getSpecifiedTimezone($item);
        $discountCycles = $this->calculateActualCouponUsages($subscriptionPlan);

        $estimationQuote = $this->quoteGenerator->generateFromItem($item, true);
        $baseGrandTotal = $baseGrandTotalWithDiscount = $estimationQuote->getBaseGrandTotal();
        $baseDiscountAmount = 0.0;
        if ($subscriptionPlan->getEnableDiscount()
            && $subscriptionPlan->getDiscountAmount()
            && ($discountCycles === null || $discountCycles > 0)
        ) {
            $estimationQuoteWithDiscount = $this->quoteGenerator->generateFromItem($item, false);
            /** @var \Magento\Quote\Model\Quote\Item $estimationItem */
            $estimationItem = $estimationQuoteWithDiscount->getAllVisibleItems()[0];
            $baseGrandTotalWithDiscount = $estimationQuoteWithDiscount->getBaseGrandTotal();
            $baseDiscountAmount = $estimationItem->getBaseDiscountAmount();
            if ($estimationItem->getHasChildren() && $estimationItem->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $baseDiscountAmount += $child->getBaseDiscountAmount();
                }
            }
        }

        $delivery = $this->billingFrequencyLabelMapper->getLabel(
            $subscriptionPlan->getFrequency(),
            $subscriptionPlan->getFrequencyUnit()
        );

        if($quote->getBaseSubtotal() != $quote->getBaseSubtotalWithDiscount())
        {
            $baseGrandTotal = $quote->getBaseSubtotal();
            $baseDiscountAmount = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount();
            $baseGrandTotalWithDiscount = $quote->getBaseSubtotalWithDiscount();
        }

        $subscription
            ->setPaymentMethod($order->getPayment()->getMethod())
            ->setCustomerId((int)$order->getCustomerId())
            ->setOrderId((int)$order->getEntityId())
            ->setProductId((int)$product->getId())
            ->setProductOptions($this->serializer->serialize($this->getItemOptions($item)))
            ->setStoreId((int)$quote->getStoreId())
            ->setShippingMethod($order->getShippingMethod())
            ->setFreeShipping($this->config->isEnableFreeShipping())
            ->setDelivery($delivery)
            ->setQty($item->getQty())
            ->setBaseDiscountAmount($baseDiscountAmount)
            ->setBaseGrandTotal($baseGrandTotal)
            ->setBaseGrandTotalWithDiscount($baseGrandTotalWithDiscount)
            ->setInitialFee($initialFee)
            ->setTrialDays($trialDays)
            ->setRemainingDiscountCycles($discountCycles)
            ->setCountDiscountCycles($discountCycles)
            ->setStatus(SubscriptionInterface::STATUS_ACTIVE)
            ->setFrequency($subscriptionPlan->getFrequency())
            ->setFrequencyUnit($subscriptionPlan->getFrequencyUnit())
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setCustomerTimezone($timezoneName)
            ->setCustomerEmail($order->getCustomerEmail());

        return $subscription;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param OrderInterface $order
     * @return SubscriptionInterface
     */
    public function saveSubscription(
        SubscriptionInterface $subscription,
        OrderInterface $order
    ): SubscriptionInterface {
        if (!$order->getIsVirtual()) {
            /** @var AddressInterface $address */
            $address = $this->addressFactory->create();
            $address->setData($order->getShippingAddress()->getData());
            $address->setEntityId(null);
            $address->setSubscriptionId($subscription->getSubscriptionId());
            $this->addressRepository->save($address);
            $subscription->setAddressId($address->getEntityId());
        }

        $this->subscriptionRepository->save($subscription);
        $subscription = $this->subscriptionRepository->getById($subscription->getId());
        
        $this->saveSubscriptionHistory($subscription);
        
        return $subscription;
    }
    
    public function saveSubscriptionHistory($subscriptionResponse)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-debug/stripe-default-order.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);   
        $logger->info(">>>>>>>>>>>>>>>>>>>>>>"); 
        $logger->info("start"); 
        $logger->info("Customer Id :: ".$subscriptionResponse->getCustomerId());
        $logger->info("Order Id :: ".$subscriptionResponse->getOrderId());
        $logger->info("Status :: ".$subscriptionResponse->getStatus()); 

        $status = $subscriptionResponse->getStatus();
        if($status == "active")
        {
            $logger->info("Here 1");
            $orderId = $subscriptionResponse->getOrderId();
            $logger->info("Here 2");
            $order = $this->OrderRepo->create()->load($subscriptionResponse->getOrderId());
            $logger->info("Here 3");
            $billfrequency = "+".$subscriptionResponse->getFrequency()." ".$subscriptionResponse->getFrequencyUnit();
            $logger->info("Here 4");
            $sub_end_date = date('Y-m-d',strtotime($billfrequency,strtotime($subscriptionResponse->getStartDate())));
            $logger->info("Here 5");
            $ProductModel = $this->_product->load($subscriptionResponse->getProductId());
            $logger->info("Here 6");
            $download_limit = $ProductModel->getAttributeText('download_limit');
            $logger->info("Here 7");
            $sub_period_lable = $ProductModel->getAttributeText('subscription_period');
            $logger->info("Here 8");
            $customer_id = $subscriptionResponse->getCustomerId();
            $logger->info("Here 9");
            $sub_from_date = date("Y-m-d",strtotime($subscriptionResponse->getStartDate()));
            $logger->info("Here 10");
            $subscription_order_start_date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
            $logger->info("Here 11");
            $payment_status = "Paid";
            $subscriptionid = $subscriptionResponse->getSubscriptionId();
            $logger->info("Here 12");
            $increment_id = $order->getIncrementId();
            $logger->info("Here 13");
            $newSubscription = $this->_subscription->create();
            $newSubscription->setSubscriptionPeriod($sub_period_lable)->setCustomerId($customer_id)
            ->setFromDate($sub_from_date)->setSubscriptionStartDate($subscription_order_start_date)
            ->setToDate($sub_end_date)->setRenewDate($sub_end_date)
            ->setStatusSuccess($payment_status)->setDownloadLimit($download_limit)
            ->setStripeCheckoutMessageId($subscriptionid)->setIncrementId($increment_id);
            $logger->info("Here 14");
            $team_plan_array = $this->_teamplanModel->getTeamSubscriptionPlans();
            $logger->info("Here 15");
            if(in_array($sub_period_lable,$team_plan_array))
            {   
                $logger->info("Here 16");
                $this->_teamplanModel->addChildSubscription($sub_period_lable,$customer_id,$sub_from_date,$subscription_order_start_date,$sub_end_date,$payment_status,$increment_id,$download_limit);
                $logger->info("Here 17");
            }
            $logger->info("Here 18");
            
            $period_full=$sub_period_lable;
            $logger->info("Here 19");
            $gtm_status=$this->scopeConfig->getValue('button/gtm_config/field1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($gtm_status)
            {             
                $logger->info("Here 20");
                $ga_id = $this->scopeConfig->getValue('button/gtm_config/ga_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $logger->info("Here 21");
                $connection = $this->_resourceConnection->getConnection();
                $subscription_results = $connection->fetchCol("SELECT `subscription_history_id` FROM `subscription_history` where `increment_id` =" . $increment_id);
                $logger->info("Here 22");
                if (count($subscription_results) > 0) {
                    $documentHost = "Offline";
                } else {
                    $documentHost = "www.slideteam.net";
                }
                $logger->info("Here 23");
                $dateToday = $this->date->gmtDate();
                $logger->info("Here 24");
                $grandTotal = $order->getGrandTotal();
                $logger->info("Here 25");
                $customerData = $this->customerFactory->load($customer_id);
                $logger->info("Here 26");
                $cid = $customerData->getCid();
                $logger->info("Here 27");
                
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
                $logger->info("Here 28");
                $customerEmail = $customerData->getEmail();
                $logger->info("Here 29");
                $customerEmailParts = explode("@",$customerEmail);
                $logger->info("Here 30");
                $emailHost = $customerEmailParts[1];
                $logger->info("Here 31");
                $customerCreatedDate = date('Y-m-d',strtotime($customerData->getCreatedAt()));
                $logger->info("Here 32");
                $customerIp = $order->getRemoteIp();
                $logger->info("Here 33");

                $url = 'http://www.google-analytics.com/collect';
                $fields = array('v' => '1', 'tid' => $ga_id, 'cid' => $cid, 'uid' => $customer_id, 'uip' => $customerIp, 'dh' => $documentHost, 't' => 'transaction', 'ti' => $orderId, 'ta' => $emailHost, 'tr' => $grandTotal, 'ts' => '0', 'cd2' => $customer_id, 'cd5' => $period_full,'cd9' => $customerCreatedDate);

                $logger->info("Here 34");
                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setHeaders(['Accept: application/json']);

                $logger->info("Here 35");
                $client->setParameterPost($fields);
                $client->setMethod(\Zend_Http_Client::POST);
                
                try {
                    $logger->info("Here 38");
                    $responseBody = $client->request()->getBody();
                    $logger->info("Here 39");
                } catch (\Exception $e) {
                    $logger->info($e->getMessage());
                    echo $e->getMessage();
                }
                $logger->info("Here 40");
                $skuSimpleProduct = $ProductModel->getSku();
                $logger->info("Here 41");
                $fields_second = array('v' => '1', 'tid' => $ga_id, 'cid' => $cid, 'uid' => $customer_id, 'uip' => $customerIp, 
                'dh' => $documentHost, 't' => 'item', 'ti' => $orderId, 'in' => $period_full, 'ic' => $skuSimpleProduct, 'iv' => 'Subscription', 'iq' => '1', 
                'ip' => $grandTotal, 'cd2' => $customer_id, 'cd5' => $period_full,'cd9' => $customerCreatedDate);

                $logger->info("Here 42");
                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setHeaders(['Accept: application/json']);
                $logger->info("Here 43");
                
                $client->setParameterPost($fields_second);
                $client->setMethod(\Zend_Http_Client::POST);
                
                try {
                    $logger->info("Here 44");
                    $responseBody = $client->request()->getBody();
                    $logger->info("Here 45");
                } catch (\Exception $e) {
                    $logger->info($e->getMessage());
                    echo $e->getMessage();
                }
            }
            
            $field4 =  $this->EmarsysHelper->isApiEnabled();
            if($field4)
            {
                $logger->info("Here 46");
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
                $logger->info("Here 47");
                $customerData = $this->customerRepository->getById($customer_id);
                $logger->info("Here 48");
                $to_date=date("Y-m-d",strtotime($sub_end_date));
                $logger->info("Here 49");
                $contact = array(
                    "1"=>$customerData->getFirstname(),
                    "2"=>$customerData->getLastname(),
                    "3"=>$customerData->getEmail(),
                    "485"=>$customer_id,
                    "488"=>$to_date,
                    "489"=>$subscription_duration,
                    "490"=>1
                );
                $logger->info("Here 50");
                $encode = json_encode($contact); 
                                
                try {
                    $logger->info("Here 51");
                    $apiHelper = $this->emarsysApiHelper;
                    $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    $logger->info("Here 52");
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
            $logger->info("Here 53");
            $newSubscription->save();
            $logger->info("Here 54");
            $order->setStripeCheckoutMessageId($subscriptionid);
            $order->setStatus("complete")->save();
            $logger->info("Here 55");
            //Zoho CRM integration start//
            if($this->zohoCRMHelper->isEnabled()){
                $logger->info("Here 56");
                $period_Name=$this->zohoCRMHelper->compareSubscription($sub_period_lable);
                $logger->info("Here 57");
                $subscriptionData = array(
                    "Priority"=>"3",
                    "Comment"=> "Create Customer Subscription",
                    "Customer_Type"=>"Subscribed",
                    "Subscription_Type"=>$period_Name,
                    "Subscription_Start_Date"=>date("Y-m-d",strtotime($sub_from_date)),
                    "Subscription_End_Date"=>date("Y-m-d",strtotime($sub_end_date))
                );
                $logger->info("Here 58");
                $this->zohoCRMHelper->editCustomer($subscriptionData,$customer_id);
            }
            $logger->info("<<<<<<<<<<<<<<<<<<<<<<");
            //Zoho CRM integration end//
        }
    }

    /**
     * @param SubscriptionPlanInterface $plan
     * @return int|null
     */
    public function calculateActualCouponUsages(SubscriptionPlanInterface $plan)
    {
        $maxUsages = $plan->getEnableDiscount()
            && $plan->getEnableDiscountLimit()
            && $plan->getNumberOfDiscountCycles()
            ? $plan->getNumberOfDiscountCycles()
            : null;

        $isTrialEnabled = $plan->getEnableTrial() && $plan->getTrialDays();
        if ($maxUsages && !$isTrialEnabled) {
            $maxUsages--;
        }

        return $maxUsages;
    }

    /**
     * @param CartItemInterface $item
     * @return array
     */
    private function getItemOptions(CartItemInterface $item): array
    {
        /** @var MagentoProduct $product */
        $product = $item->getProduct();
        /** @var DataObject $request */
        $request = $item->getBuyRequest();

        $options = [];
        if ($typeOptions = self::TYPE_OPTIONS[$product->getTypeId()] ?? null) {
            $options = array_intersect_key($request->getData(), array_flip($typeOptions));
        }

        $customOptions = $request->getData('options');
        $customOptions && $options['options'] = $customOptions;

        return $options;
    }
}
