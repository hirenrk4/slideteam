<?php

namespace Tatva\Subscription\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Tatva\Subscription\Model\Subscription;
use Tatva\Subscription\Model\SubscriptionInvitation;
use Tatva\Subscription\Model\Mostdownload;
use Tatva\Subscription\Model\Shareanddownloadproducts;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{

    const XML_PATH_SUBSCRIPTION = 'subscription/';
    protected $_customerSession;

    public function __construct(\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request,
        ResourceConnection $resourceConnection,
         \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlInterface,
        Subscription $model,
        SubscriptionInvitation $subscriptionInvitation,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Mostdownload $mostdownload,
        Shareanddownloadproducts $shareanddownloadble,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory
        ) 

    {
        $this->_urlInterface = $urlInterface;
        $this->model = $model;
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->resourceConnection = $resourceConnection;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
        $this->_customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->mostdownload=$mostdownload;
        $this->shareanddownloadble=$shareanddownloadble;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->_subscriptionInvitation = $subscriptionInvitation;
        parent::__construct($context);
    }


    public function getPricingUrl() {
       $baseUrl=$this->_urlInterface->getBaseUrl();
       return $baseUrl. 'pricing/';
    }


    public function subscriptionFlowParameters() {
        return array('referer' => $this->_getRequest()->getParam('referer'), 'download' => $this->_getRequest()->getParam('download'), 'prd_id' => $this->_getRequest()->getParam('prd_id'), 'cart' => $this->_getRequest()->getParam('cart'),'last_url' => $this->_getRequest()->getParam('last_url'),'wishlist'=>$this->_getRequest()->getParam('wishlist'));
    }


    public function getCustomerId(){
        $customer = $this->_customerSession->create();
        return $customer->getCustomer()->getId();
    }


    public function getConfigureProductId() {
        $product_id = $this->scopeConfig->getValue('subscription_options/subscription/subscription_product', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $product_id;
    }


    public function isLoggedIn(){
        $customer = $this->_customerSession->create();
        return $customer->isLoggedIn();
    }


    public function getConfigValue($field, $storeId = null){
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
            );
    }


    public function getGeneralConfig($code, $storeId = null){

        return $this->getConfigValue(self::XML_PATH_HELLOWORLD .'general/'. $code, $storeId);
    }


    public function checkDownloads($subscriptions, $product_id="", $all = false) {
        $downloaded = -1;
        $product_allowed_to_download = false;
        $customer_id = $this->getCustomerId();


        if (is_object($subscriptions) && $subscriptions->getId() != "") {
            if (!$customer_id) {
                $customer_id = $subscriptions->getCustomerId();
            }

            $current_gmt_date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
            $current_date = strtotime($current_gmt_date);
            $from_date = strtotime($subscriptions->getFromDate());

            if($subscriptions->getSubscriptionStartDate())
            {
                 $from = $subscriptions->getSubscriptionStartDate();
                 $subr_from_date = strtotime($from);
             }
             else{
                 $subr_from_date = $from_date;
             }

            $to_date = strtotime($subscriptions->getToDate());
            $downloaded = $this->mostdownload->downloadCheckPerCustomerSubscription($customer_id, $subr_from_date);
            if ($current_date >= $from_date && $current_date <= $to_date && $subscriptions->getData("status_success") != "Failed") {
                $product_allowed_to_download = $this->mostdownload->productAllowedToDownload($customer_id, $from_date, $product_id);
            }
        }

        if ($all)
            return array("downloaded" => $downloaded, "allowed" => $product_allowed_to_download);
        else
            return $downloaded;
    }


    public function checkDownloadsForRenderer($subscriptions, $product_id="", $all = false) {
        $downloaded = -1;
        $product_allowed_to_download = false;
        $customer_id = $this->getCustomerId();


        if (is_array($subscriptions) && $subscriptions['subscription_history_id'] != "") {
            if (!$customer_id) {
                $customer_id = $subscriptions['customer_id'];
            }
            $current_gmt_date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
            $current_date = strtotime($current_gmt_date);
            $from_date = strtotime($subscriptions['from_date']);
   
            if(!empty($subscriptions['subscription_start_date']) && $subscriptions['subscription_start_date'] != 0)
            {
                $from = $subscriptions['subscription_start_date'];
                $subr_from_date = strtotime($from); 
            }
            else
            {
                $subr_from_date = $from_date; 
            }
   
            $to_date = strtotime($subscriptions['to_date']);

            if ($current_date >= $from_date && $current_date <= $to_date && $subscriptions['status_success'] != "Failed") {
                $downloaded = $this->mostdownload->downloadCheckPerCustomerSubscription($customer_id, $subr_from_date);
                $product_allowed_to_download = $this->mostdownload->productAllowedToDownload($customer_id, $from_date, $product_id);
            }
        }
       
        if ($all)
            return array("downloaded" => $downloaded, "allowed" => $product_allowed_to_download);
        else
            return $downloaded;
    }

    public function getParamStringFun($params) {
        $param_string = "";
        if (is_array($params) && count($params) > 0)
            foreach ($params as $key => $p) {
                $param_string.=$key . "/" . $p . "/";
            }
        return $param_string;
    }

    public function getDecrypted($params){
       return $this->encryptor->decrypt($params);
    }

    public function getSubscriptionInvitation($email){

        $collection = $this->_subscriptionInvitation->getCollection();
        $collection->addFieldToFilter('customer_email',$email);
        $collection->setOrder('invitation_id','DESC');
        $collection->setPageSize(1);
        
        return $collection;
    }

    public function getInvitationEmail($customer_email){
       $collection = $this->_subscriptionInvitation->getCollection();
       $collection->addFieldToSelect(array('customer_email'))->addFieldToFilter('customer_email',$customer_email);
       $collection->setPageSize(1);
       $result = $collection->getFirstItem();

       return $result;
    }

    public function getSubscription($parent_id){

       $collection = $this->_subscriptionInvitation->getCollection();
       $collection->addFieldToFilter('parent_customer',$parent_id);

       return $collection;
    }

    // public function getSubscriptionInvitationEmail($customer_email){
       
    //    $connection = $this->resourceConnection->getConnection();
    //    $query = "SELECT invitation_id FROM subscription_invitation WHERE `customer_email`='".$customer_email."'";
    //    $result = $connection->fetchOne($query);
    //    return $result;
    // }

    public function getChildCustomerSubscription($id){

        $collection = $this->model->getCollection()->addFieldToFilter('customer_id',$id); 
        $collection->setOrder('subscription_history_id','DESC');
        $collection->setPageSize(1);        
        $result = $collection->getFirstItem(); 
        
        return $result;
    }
    
    public function getParentSubscriptionHistory($id){

       $collection = $this->model->getCollection()->addFieldToFilter('customer_id',$id);
       $collection->setOrder('subscription_history_id','DESC');
       $collection->setPageSize(1);
       $result = $collection->getFirstItem(); 
       
       return $result;
    }

    public function getSubscriptionHistory($id){

        $current_gmt_date = $this->_dateFactory->create()->gmtDate('Y-m-d');

        $collection = $this->model->getCollection()->addFieldToFilter('customer_id',$id);
        $collection->addFieldToFilter('from_date',array('lteq'=>$current_gmt_date));
        $collection->addFieldToFilter('to_date',array('gteq'=>$current_gmt_date));
        $collection->setOrder('subscription_history_id','DESC');
        $collection->setPageSize(1);
        //$result = $collection->getFirstItem(); 

        return $collection;
    }

    public function getSubscriptionPlan($id){

        $collection = $this->model->getCollection()->addFieldToSelect(array('subscription_period'))->addFieldToFilter('customer_id',array('eq'=>$id));
        $collection->setOrder('subscription_history_id','DESC');
        $collection->setPageSize(1);
        $result = $collection->getFirstItem(); 

        return $result;
    }

    public function getSubscriptionRemaining($id){
        
        $collection = $this->_subscriptionInvitation->getCollection();
        $collection->addFieldToSelect(array('parent_customer'))->addFieldToFilter('parent_customer',array('eq'=>$id));
        
        return $collection->getSize();
    }

    public function getAllChildSubscriptionHistory($id,$childemail){

       //$id = $this->getCustomerId();
       //$collection = $this->_subscriptionInvitation->getCollection()->addFieldToFilter('parent_customer',$id);

       $collection = $this->_subscriptionInvitation->getCollection()->addFieldToFilter(['parent_customer','customer_email'],[['eq'=>$id],['eq'=>$childemail]]);

       return $collection;
    }

    public function getChildSubscriptionHistory($id){

       //$id = $this->getCustomerId();
       $collection = $this->_subscriptionInvitation->getCollection()->addFieldToFilter('parent_customer',$id);
       $collection->addFieldToFilter("child_customer_id",array("notnull" => true))->addFieldToFilter("child_customer_id",array("neq" => 0));

       //$collection->addFieldToFilter("child_customer_id",array("notnull" => true));

       return $collection;
    }

    public function getCustomerPlanName($id){
        $customer = $this->customerRepository->getById($id);
        $plan_name = $customer->getCustomAttribute('customer_plan_name');
        if ($plan_name) {
            return $plan_name->getValue();    
        }
        return;
    }

    public function getUsers($parent_id){
        $customer = $this->customerRepository->getById($parent_id);
        $users = $customer->getCustomAttribute('no_of_users');
        if ($users) {
            return $users->getValue() - 1;    
        }
        return;
    }
}