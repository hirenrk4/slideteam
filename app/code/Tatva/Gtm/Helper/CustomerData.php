<?php
/**
 * @category   Tatva
 * @package    Tatva_Gtm
 */
namespace Tatva\Gtm\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Tatva\Gtm\Helper
 */
class CustomerData extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
     * @var Context
     */
    protected $context;
    protected $checkoutSession;    
    protected $_coreSession;
    protected $_resourceConnection;   
    protected $_connection;
    protected $subscriptionModel;
    protected $_gtmcookie;
    
	public function __construct(
		Context $context,
		\Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\ResourceConnection $gtmresourceConnection,        
        \Tatva\Subscription\Model\Subscription $subscriptionModel,
        \Tatva\Gtm\Model\Cookie\GtmCookie $gtmcookie
	)
	{
        $this->context = $context;
        $this->checkoutSession = $checkoutSession;
        $this->_coreSession = $coreSession;
        $this->_resourceConnection = $gtmresourceConnection;        
        $this->subscriptionModel = $subscriptionModel;
        $this->_gtmcookie = $gtmcookie;
        parent::__construct($context);
	}

	public function getCustomerData($customerId)
	{
		$this->_coreSession->start();
        $cus_create = $this->_coreSession->getCustomerCreate();
        $this->_coreSession->setCustomerCreate(0);
        if(empty($cus_create)) :
            $cus_create = 0;
        endif;
        if($customerId)
        {   
            $this->_connection = $this->_resourceConnection->getConnection();
            $result = $this->_connection->fetchCol("SELECT `type` FROM mageplaza_social_customer where `customer_id` = '".$customerId."' ORDER BY `social_customer_id` DESC limit 1");

            if (is_array($result) && !empty($result))
            {
                if($result[0]=="Google")
                {
                  $joinedFrom = "google";  
                }
                elseif($result[0]=="Facebook")
                {
                  $joinedFrom = "facebook";    
                }
            }
            else
            {
                $joinedFrom = 'System';
            }

            $result = $this->_connection->fetchCol("SELECT `from_date` FROM subscription_history where `customer_id` =".$customerId." AND `status_success` != 'Failed' order by subscription_history_id asc limit 1");
            $first_purchase_date = '';
            if(isset($result[0])){
                $date = $result[0];
                if($date){
                    $date = strtotime($date);
                    $first_purchase_date = date("Y-m-d", $date);
                }
            }         
            
            $result = $this->_connection->fetchCol("SELECT COUNT(`product_id` ) FROM productdownload_history_log WHERE  `customer_id` =" . $customerId);
            if (is_array($result) && !empty($result)) {
                $numberOfProductsDownloaded = $result[0];
            } else {
                $numberOfProductsDownloaded = null;
            }       
            
            $customerData = $this->subscriptionModel->getCustomerType($customerId);
            if(is_array($customerData))
            {
                $customerType = $customerData['customerType'];
                if($customerType == "Download Limit Reached" || $customerType == "Active")
                {
                    $customerSubscription = $customerData['latestSubscription'];
                    $customerType = "Active";
                }
                else
                {
                    $customerSubscription = $customerType;
                    $customerType = "Expired";
                }
            }
            else
            {
                $customerSubscription = $customerData;
                $customerType = "Free";
            }
           
            $name_sub = "customer_sub";
            $value_sub = $customerType == 'Free' ? 0 : 1;
            $this->_gtmcookie->set($name_sub,$value_sub);           

            $subscription_pay_results = $this->_connection->fetchAll("SELECT `paypal_id` ,`two_checkout_message_id`,`subscription_history_id` FROM `subscription_history` where `status_success` != 'Failed' AND `customer_id` =" . $customerId);

            $paypal_ids_array = array();
            $two_checkout_ids_array = array();
            $grandAmount = $totalPaypalAmount = $totalTwoCheckoutAmount = 0;
            
            if (is_array($subscription_pay_results) && !empty($subscription_pay_results))
            {
                foreach ($subscription_pay_results as $values)
                {
                    if (isset($values['paypal_id']) && $values['paypal_id'] != "NULL" && $values['paypal_id'] != 0)
                    {
                        $paypal_ids_array[] = $values['paypal_id'];
                    }
                    elseif (isset($values['two_checkout_message_id']) && $values['two_checkout_message_id'] != "NULL" && $values['two_checkout_message_id'] != 0)
                    {
                        $two_checkout_ids_array[] = $values['two_checkout_message_id'];
                    }
                }
                if (is_array($paypal_ids_array) && !empty($paypal_ids_array))
                {
                    $paypal_ids = implode(",", $paypal_ids_array);
                    $paypal_data = $this->_connection->fetchCol("SELECT SUM(`amount`) AS 'amount' FROM paypal_result where `id` IN (" . $paypal_ids . ")");
                    $totalPaypalAmount = $paypal_data[0];
                }

                if (is_array($two_checkout_ids_array) && !empty($two_checkout_ids_array))
                {
                    $two_checkout_ids = implode(",", $two_checkout_ids_array);
                    $two_checkout_data = $this->_connection->fetchCol("SELECT SUM(`invoice_usd_amount`) AS 'amount' FROM 2checkout_ins where `id` IN (" . $two_checkout_ids . ")");
                    $totalTwoCheckoutAmount = $two_checkout_data[0];
                }

                $grandAmount = $totalPaypalAmount + $totalTwoCheckoutAmount;
                $grandAmount = round($grandAmount,2);
            }            
        }
        else
        {
            //$customerId = $this->currentCustomer->getCustomerId();
            $cus_create = 0;
            $joinedFrom = $customerEmail =  $registrationDate = $customerName =  NULL;
            $customerSubscription = $grandAmount = $numberOfProductsDownloaded = $first_purchase_date = NULL;
        }        

        return [
            'customerId' => $customerId,
            'cartItemsIds' => $this->getProductIdOfAddedToCartProduct(),
            'cusCreate'  => $cus_create,            
            'accountCreationType' => $joinedFrom,
            'customerSubscription'=> $customerSubscription,
            'lifeTimeValue'=> (float)$grandAmount,
            'numberOfProductsDownloaded' => $numberOfProductsDownloaded,            
            'firstPurchaseDate' => $first_purchase_date
        ];
	}
	/**
     * get ProductIds which are added to cart
     * @return array
     */
    protected function getProductIdOfAddedToCartProduct()
    {
        $cartProductIds = [];
        $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
        if(count($items) > 0){
            foreach ($items as $item) {
                $cartProductIds[] = $item->getProduct()->getId();
            }
        }
        return $cartProductIds;
    }
}