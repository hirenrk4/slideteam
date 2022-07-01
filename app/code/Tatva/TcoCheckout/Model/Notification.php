<?php

namespace Tatva\TcoCheckout\Model;

use Exception;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * As we need to update subscription_history table according INS we rewrited the Notification model 
 */
class Notification extends \Tco\Checkout\Model\Notification 
{
    
    const  RECURRING_INSTALLMENT_SUCCESS = "RECURRING_INSTALLMENT_SUCCESS";
    
    const   RECURRING_STOPPED = "RECURRING_STOPPED";

    const   RECURRING_INSTALLMENT_FAILED = "RECURRING_INSTALLMENT_FAILED";

    protected   $_ins;

    protected   $_tcoInsId;

    protected   $_statusSuccess;

    protected   $_ipnCheck;

    protected   $_scopeConfig;

    protected   $_subHistoryStatusNeedToCheck;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    protected $_quoteCollection;

    protected $_orderCollectionFactory;    
    
    protected $_orderCreate;

    protected $_ipnDataFactory;


    function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Tco\Checkout\Model\Checkout $tcoCheckout,
        \Tco\Checkout\Model\Api $tcoApi,
        \Tco\Checkout\Model\Paypal $tcoPaypal,
        OrderSender $orderSender,
        \Tco\Checkout\Model\Ins $ins,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Tatva\Subscription\Model\IpncheckFactory $ipnCheck,        
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Tco\Checkout\Helper\OrderCreate $orderCreate,
        \Tatva\TcoCheckout\Model\IpnDataFactory $ipnDataFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $datetimeFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        parent::__construct($orderFactory,$tcoCheckout,$tcoApi,$tcoPaypal,$orderSender);
        $this->_ins = $ins;
        $this->_ipnCheck = $ipnCheck;
        $this->_scopeConfig = $scopeConfig;
        $this->_subHistoryStatusNeedToCheck = false;
        $this->_orderFactory = $orderFactory;        
        $this->_quoteCollectionFactory = $quoteCollection;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderCreate = $orderCreate;   
        $this->_ipnDataFactory = $ipnDataFactory;
        $this->_dateTimeFactory = $datetimeFactory;
        $this->_customerFactory = $customerFactory;
        
    }

    /**
     * Needed to rewrite as we need to add our two additional message_type for recurring payments and its handles and we also need to update the subscription_history table for all actions
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function processNotification($params)
    {

        // Save ins into 2checkout_ins table
        
        //$date = date('dmY',time());
        
        
        if($params['message_type'] == $this::ORDER_CREATED)
        {    
            
            $orderid = $params['vendor_order_id'];            
            $orderCollection = $this->_orderCollectionFactory->create();
            $ordCollection = $orderCollection->addFieldToFilter('increment_id', ['eq' => $orderid]);            
            $count = $ordCollection->getSize();
            
            if(empty($orderid))
            {
                $quoteCollection = $this->_quoteCollectionFactory->create();
                $quoteCollection->addFieldToFilter('customer_email',['eq'=>$params['customer_email']]);
                $quoteCollection->getSelect()->order('entity_id desc');
                $quoteCollection->getSelect()->limit(1);
                $count = $quoteCollection->count();
                
                if($count == 1)
                {           
                            
                    foreach($quoteCollection as $key => $quote)
                    {
                        $reservedId = $quote->getReservedOrderId();
                        if(!empty($reservedId)) :
                            $this->_orderCreate->CreateOrder($quote,$params);
                        else :
                            
                        endif;
                    }   
                }
            }
            else
            {
                sleep(15);
                $orderid = $params['vendor_order_id'];
                $orderCollection = $this->_orderCollectionFactory->create();
                $ordCollection = $orderCollection->addFieldToFilter('increment_id', ['eq' => $orderid]);
                $count = $ordCollection->getSize();
                if(empty($count))
                {               
                    $quoteCollection = $this->_quoteCollectionFactory->create();
                    $quoteCollection->addFieldToFilter('reserved_order_id',['eq'=>$orderid]);
                    $quoteCollection->getSelect()->order('entity_id desc');
                    $quoteCollection->getSelect()->limit(1);
                    $count = $quoteCollection->count();
                    
                    if($count == 1)
                    {        
                        
                        foreach($quoteCollection as $key => $quote)
                        {
                            $this->_orderCreate->CreateOrder($quote,$params);
                        }
                    }
                } 
            }           
        }
        else
        {
        	if(!isset($params['is_db']))
            {

                //$customerEmail = $this->_customerSession->getCustomer()->getEmail();

                if(!isset($params["vendor_order_id"]) && isset($params["merchant_order_id"]) && $params["merchant_order_id"]!=""){
                    $vendorOrderId = $params["merchant_order_id"];
                }
                else{
                    $vendorOrderId = $params['vendor_order_id'];
                }

                $this->_order = $this->_orderFactory->create()->loadByIncrementId($vendorOrderId);
                $customerId = $this->_order->getCustomerId();
                $customerObj = $this->_customerFactory->create()->load($customerId);
                $customerEmail = $customerObj->getEmail();
                
                $ipnTime = $this->_dateTimeFactory->create()->gmtDate();
                $params['is_db'] = 1;
                $postfields = serialize($params);
                $this->_ipnDataFactory->create()->setIpnData($postfields)->setIpnTime($ipnTime)->setCustomerEmail($customerEmail)->setIsError(0)->save();
                exit;                
            }

            if($params['message_type'] == "RECURRING_RESTARTED")
            {
                exit;
            }
        }


        $this->addInsToTbl($params);
        $order = $this->_getOrder($params);

        if ($order) {
            //$this->setPaymentMethod($this->_paymentMethod = $order->getPayment()->getMethod());
            //For M1 payment_method_code = tco and For m2 payment_method_code = tco_checkout
             $this->setPaymentMethod(\Tco\Checkout\Model\Checkout::CODE);
            if ($this->_validateResponse($params['sale_id'], $params['invoice_id'], $params['md5_hash'])) {
                try {
                    $messageType = $params['message_type'];

                    switch ($messageType) {
                        case $this::ORDER_CREATED:
                            $this->_processOrderCreated($params);
                            break;
                        case $this::INVOICE_STATUS_CHANGED:
                            $this->_processInvoiceStatusChanged($params);
                            break;
                        case $this::FRAUD_STATUS_CHANGED:
                            $this->_processFraudStatusChanged($params);
                            break;
                        case $this::REFUND_ISSUED:
                            $this->_processRefundIssued($params);
                            break;

                        // Additionaly added as per our requirement
                        case $this::RECURRING_INSTALLMENT_SUCCESS:
                            $this->_processRecurringInstallementSuccess($params);
                            break;

                        case $this::RECURRING_STOPPED:
                            $this->_processRecurringStopped($params);
                            break;

                        // This is not implemented in M1 Need to verify some cases
                        case $this::RECURRING_INSTALLMENT_FAILED:
                            // $this->_processRecurringStopped($params);
                            
                            break;
                            
                        default:
                            throw new Exception('Cannot handle INS message type for message: "%s".', $params['message_id']);
                    }

                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $comment = $this->_createNotificationComment(sprintf('Error: "%s"', $e->getMessage()));
                    $comment->save();
                    throw $e;
                }
            } else {
                throw new Exception(sprintf('MD5 hash mismatch for 2Checkout notification message: "%s".', $params['message_id']));
            }
        } else {
            throw new Exception(sprintf('Could not locate order: "%s".', $params['vendor_order_id']));
        }

    }


    protected function _getOrder($params)
    {       
        if(!isset($params["vendor_order_id"]) && isset($params["merchant_order_id"]) && $params["merchant_order_id"]!=""){
            $vendorOrderId = $params["merchant_order_id"];
        }
        else{
            $vendorOrderId = $params['vendor_order_id'];
        }

        $this->_order = $this->_orderFactory->create()->loadByIncrementId($vendorOrderId);
        if (!$this->_order->getId()) {
            exit;
            //throw new Exception(sprintf('Wrong order ID: "%s".', $vendorOrderId));
        }

        return $this->_order;
    }


    protected function getSubscriptionRow($order_increment_id)
    {
        $subscription_history = $this->_ins->checkSubscriptionAvailibility($order_increment_id);
        return $subscription_history;
    }



    /**
     * Save INS message to 2checkout_ins table
     * @param array $params INS from 2checkout server
     */
    protected function addInsToTbl($params)
    {
        try {
            $this->_tcoInsId = $this->_ins->saveInsData($params);

            if($this->_tcoInsId == false || $this->_tcoInsId == ""){
                throw new Exception('Cannot add INS message into 2checkout_ins table');
            }
        } catch (Exception $e) {
            
            throw $e;
        }
    }


    protected function _processOrderCreated($params)
    {
        parent::_processOrderCreated($params);
        $invoice_on_order = $this->_paymentMethod->getConfigData('invoice_before_fraud_review');
        $invoice_status = $params['invoice_status'];
        $recurring = $params['recurring'];
        if($invoice_on_order || ($invoice_status == "approved" && $recurring == "1") ){
            $this->_statusSuccess = "Paid" ;
            // Update subscription_history
            $this->updateSubscriptionHistoryTco($params,$this->_subHistoryStatusNeedToCheck);        
        }
    }


    protected function _processInvoiceStatusChanged($params)
    {
    	//$this->OrderCheck($params);

        parent::_processInvoiceStatusChanged($params);
        $this->_subHistoryStatusNeedToCheck = true;
        if ($params["invoice_status"] == 'deposited' && $params["recurring"] == '1') {
            $this->_statusSuccess = "Paid" ;

            // Update subscription_history
            $this->updateSubscriptionHistoryTco($params,$this->_subHistoryStatusNeedToCheck);
        }
        
        $this->_order->setStatus("complete");
        $this->_order->save();
    }


    protected function _processFraudStatusChanged($params)
    {
    	//$this->OrderCheck($params);

        parent::_processFraudStatusChanged($params);
        if ($params['fraud_status'] == 'fail') {
            $this->_statusSuccess = "Failed";
        }
        elseif ($params['fraud_status'] == 'pass' || $params['fraud_status'] == 'wait') {
            $this->_statusSuccess = "Paid" ;       
        }

        // Update subscription_history
        // We have commented this due to double entries in subscription_history because of simulteneous entries in table
        // $this->updateSubscriptionHistoryTco($params,$this->_subHistoryStatusNeedToCheck);
    }


    // Enhancement suggested in trello task "#157 - Tco refund INS development for Subscription module"
    // Need to update subscription_history table structure by adding "Refund Successful" status for status_success field
    /*protected function _processRefundIssued($params)
    {
        parent::_processRefundIssued($params);
        $this->_statusSuccess = "Refund Successful" ;

        // Update subscription_history
        $this->updateSubscriptionHistoryTco($params,$this->_subHistoryStatusNeedToCheck);       
    }*/


    /**
     * Here as of old code no need to update subscription_history by this class's method but it must be done by saveSubscription method
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    protected function _processRecurringInstallementSuccess($params)
    {
        $this->_ipnCheck->create()->saveSubscription($params,NULL,$this->_tcoInsId);
    }


    protected function _processRecurringStopped($params)
    {
        $this->_statusSuccess = isset($params["recurring"]) && $params["recurring"] == "1" ? "Unsubscribed" : "";

        // Update subscription_history
        $this->updateSubscriptionHistoryTco($params,$this->_subHistoryStatusNeedToCheck);
    }

    protected function OrderCheck($params)
    {
    	$orderid = $params['vendor_order_id'];
    	if(!empty($orderid))
    	{
	        $orderCollection = $this->_orderCollectionFactory->create();
	        $ordCollection = $orderCollection->addFieldToFilter('increment_id', ['eq' => $orderid]);
	        $count = $ordCollection->getSize();
	        if(empty($count))
	       	{	        	
	        	sleep(25);
	        }
    	}
    }

    protected function updateSubscriptionHistoryTco($params,$subHistoryStatusNeedToCheck = false)
    {
        $order_increment_id = $params['vendor_order_id'];
        if(!empty($order_increment_id))
        {
            $order = $this->_orderFactory->create()->loadByAttribute("increment_id", $order_increment_id);
        }
        $messageType = $params['message_type'];
        $subscription_history = $this->getSubscriptionRow($order_increment_id);
        $sub_history_need_to_update = true;
        
        if (is_object($subscription_history) && $subscription_history->getId() != "") {
            // This will only useful for "INVOICE_STATUS_CHANGED" case
            

            if($subHistoryStatusNeedToCheck){
                $status_success = $subscription_history->getData("status_success");
                $sub_history_need_to_update = $status_success != "Unsubscribed" && $status_success != "Requested Unsubscription";
            }
            if($sub_history_need_to_update){

                if($messageType == "RECURRING_STOPPED" && $order->getStatus() != "complete")
                {
                    $this->_statusSuccess = 'Cancelled';
                    $subscription_history->setData("download_limit",'0');   
                }

                $subscription_history->setData("two_checkout_message_id", $this->_tcoInsId);
                $subscription_history->setData("status_success",$this->_statusSuccess);
                $subscription_history->save();
            }
        }
        else{
            // Need to work
            $this->_ipnCheck->create()->saveSubscription($params,NULL,$this->_tcoInsId);
        }
    }
}