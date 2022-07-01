<?php
namespace Tatva\Paypalrec\Plugin\Model;

use Exception;

/**
 * 	Need to update paypal result and subscription history table after order placed or ipn received
 */
class Ipn 
{
	/**
     * @var \Tatva\Paypalrec\Model\ResultFactory
     */
    protected	$_paypalrecResultFactory;
        
    protected	$_subscription;
    
    protected	$_ipnCheck;
    
    protected	$_subscriptionCollection;

    protected	$_request;

    protected	$_postData;

    protected	$_scopeConfig;

    protected	$_logger;

    protected 	$_paypalResult;

	function __construct(
		\Tatva\Subscription\Model\SubscriptionFactory $subscription,
		\Tatva\Paypalrec\Model\ResultFactory $paypalrecResultFactory,
		\Tatva\Subscription\Model\IpncheckFactory $ipnCheck,
		\Tatva\Subscription\Model\ResourceModel\Subscription\Collection  $subscriptionCollection,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Psr\Log\LoggerInterface $logger
		){
		$this->_paypalrecResultFactory = $paypalrecResultFactory; 
		$this->_subscription = $subscription;
		$this->_ipnCheck = $ipnCheck;
		$this->_subscriptionCollection = $subscriptionCollection;
		$this->_request = $request;
		$this->_scopeConfig = $scopeConfig;
		$this->_logger = $logger;
	}

	/**
	 * For debugging only
	 * log paypal's IPN request data in /var/log/paypal_ipn.log file
	 * Comment function after debugging.
	 */
	/*public	function beforeProcessIpnRequest(){
		$data = $this->_request->getPostValue();
	}*/

	/**
	 * 1 - Save Ipn in paypal_result
	 * 2 - Add entries in subscription_history
	 * 3 - Update order in if needed
	 * @return [type] [description]
	 */
	public	function afterProcessIpnRequest(){	
		
		$this->_postData = $this->_request->getPostValue();
		$txn_type = $this->getRequestData('txn_type');
		
		if($txn_type != "invoice_payment" && $txn_type != "recurring_payment_skipped" && $txn_type != "subscr_failed" && $txn_type != "recurring_payment_suspended_due_to_max_failed_payment" && $txn_type != "new_case" && $txn_type != "adjustment")
		{			
			$this->updatePaypalResult();
			$this->updateSubscriptionHistoryPaypal();
		}
	}

	/**
	 * Add entry in Paypal result table from IPN Data and set $this->_paypalResult.
	 * @throws \Exception
	 * @return [type] [description]
	 */
	protected function updatePaypalResult(){		

		$paypalResultData = array();
		$isTestEnvironment = $this->_scopeConfig->getValue("paypal/wps/sandbox_flag", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$isTestIpn = isset($this->_postData['test_ipn']) && $this->_postData['test_ipn'] == 1 ? true : false;
		$txn_type = $this->getRequestData('txn_type');

		$paypalResultData['trasaction_type'] = $txn_type;
		$paypalResultData['reattempt'] = $this->getRequestData('reattempt');
		$paypalResultData['period'] = $this->getRequestData('period3') == null ? $this->getRequestData('payment_cycle') : $this->getRequestData('period3');	//in new case ipn "payment_cycle"
		$paypalResultData['txn_id'] = $this->getRequestData('txn_id');
		$paypalResultData['amount'] = $this->getRequestData('payment_gross') == null ? $this->getRequestData('amount') : $this->getRequestData('payment_gross');		//in new case ipn "amount"
		/* amount value 0 to set initial_payment_amount */
		if ($paypalResultData['amount'] == 0) {
			$paypalResultData['amount'] = $this->getRequestData('initial_payment_amount');
		}
		/* amount value 0 to set initial_payment_amount */
		$paypalResultData['subscription_date'] = $this->getRequestData('time_created') == null ? $this->getRequestData('payment_date') : $this->getRequestData('time_created');		
		$paypalResultData['sellers_id'] = $this->getRequestData('receiver_id');
		$paypalResultData['payers_id'] = $this->getRequestData('payer_id');
		$paypalResultData['increment_id'] = $this->getRequestData('invoice') == null ? $this->getRequestData('rp_invoice_id') : $this->getRequestData('invoice');		//in new case ipn "rp_invoice_id"
		$paypalResultData['paypal_id'] = $this->getRequestData('subscr_id') == null ? $this->getRequestData('recurring_payment_id') : $this->getRequestData('subscr_id');		//in new case ipn "recurring_payment_id"
		$paypalResultData['result_data_from'] = "ipn";
		$paypalResultData['transaction_log'] = print_r($this->_postData, true);
		$paypalResultData['test_ipn'] = $isTestIpn;
		$paypalResultData['success'] = isset($this->_postData['payment_status']) && $this->_postData['payment_status'] == "Completed" ? "1" : "0";

		// Old M1 type's Subscription new = subscr_signup , recurring = subscr_payment  , subscr_cancel
		// $paypalResultData['recuuring'] = $txn_type == "subscr_payment" || $txn_type == "subscr_signup" ? "1" : "0";
		// New M2 ipn txn_types = recurring_payment_profile_created ,cart ,recurring_payment,express_checkout
		
		$success_txn_types = ["subscr_payment","subscr_signup","recurring_payment_profile_created","recurring_payment","express_checkout"];
		$paypalResultData['recurring'] = in_array($txn_type, $success_txn_types) ? "1" : "0";

		try {
			/**
			 * Check whether transction type is subscription new/recuuring not other paypal like cart for normal product purchase
			 */
			if ($txn_type != "") {
				$result = $this->_paypalrecResultFactory->create();
				$result->setData($paypalResultData);
				$result->save();
				$this->_paypalResult = $result;
			}			
		} catch (Exception $e) {
			$this->_logger->debug(var_export($e->getMessage(), true));
			throw $e;
		}		
	}

	/**
	 * Update subscription_history table according to IPN Data
	 * Cases
	 * 1 : New Subscription add entry in subscription_history accordingly means success/failure
	 * 2 : Recurring payment IPN , update data in subscription_history
	 * 3 : Cancel subscription , Unsubscribe
	 * @todo need to throw exception if IPN is not from our defined one
	 * @return [type] [description]
	 */
	protected function updateSubscriptionHistoryPaypal(){
		$isTestEnvironment = $this->_scopeConfig->getValue("paypal/wps/sandbox_flag", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$isTestIpn = isset($this->_postData['test_ipn']) && $this->_postData['test_ipn'] == 1 ? true : false;
		$txn_type = $this->getRequestData('txn_type');
		if($txn_type == "cart"){
			return;
		}
		$txn_types_needed_to_update_sub_history = ["subscr_payment","subscr_signup","subscr_cancel","subscr_eot","cart","recurring_payment","express_checkout","recurring_payment_profile_cancel","recurring_payment_expired","recurring_payment_profile_created"];
		$needToUpdateSubHistory = in_array($txn_type, $txn_types_needed_to_update_sub_history);
	    try {
			if ($needToUpdateSubHistory){		
		        
		        /**
		         * To controll subscription_history table update flow we have moved whole logic in ipn check model
		         * For new subscription there is cart ipn and invoice
		         * For recurring subscription there is recurring_payment ipn and rp_invoice_id and we have to map it with tatva_pp_recurring_mapper table
		         */
		        
		        if (!empty($this->_postData['invoice']) || (!empty($this->_postData['recurring_payment_id'])) ) {
		        	if(!is_null($this->_paypalResult)){
			    		$paypal_result_id = $this->_paypalResult->getId();
			        	$txn_id = $this->_paypalResult->getTxnId();
	        			$this->_ipnCheck->create()->saveSubscription($this->_postData,$paypal_result_id ,"",$txn_id,$txn_type);
			    	}		    
		        }
		        else{
		        	throw new \Exception('Paypal response has no increment_id/invoice or recurring profile id.');
		        }        	
	        } 
	        else{
	        	throw new \Exception('Flow for this type of IPN is not yet defined. IPN txn_type : '.$txn_type);
	        }
	    }
        catch (Exception $e) {
        	$this->_logger->debug(var_export($e->getMessage(), true));
        	
			throw $e;
        }  		
	}


	/**
     * IPN request data getter
     *
     * @param string $key
     * @return array|string
     */
    public function getRequestData($key = null){
        if (null === $key) {
            return $this->_postData;
        }
        return isset($this->_postData[$key]) ? $this->_postData[$key] : null;
    }
}
