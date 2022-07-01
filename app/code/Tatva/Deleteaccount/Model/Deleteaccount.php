<?php 
namespace Tatva\Deleteaccount\Model;
use Magento\Store\Model\ScopeInterface;

class Deleteaccount extends \Magento\Framework\Model\AbstractModel
{
	protected $_customerSession;
	protected $_dateTime;
	protected $_subscription;
	protected $_subscriptionbkp;
	protected $_customer;
	protected $_scopeConfig;
	protected $_registry;
	protected $_storeManager;
	protected $_transportBuilder;
	protected $_deletedcustomerbkp;
	protected $_productCollection;
	protected $_productdownloadhistorylog;
	protected $salesrulecollection;
	/**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;

	const XML_PATH_EMAIL_DELETEACCOUNT = 'tatva/deleteaccount/email';

	public function __construct
	(
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Customer\Model\Customer $customer,
		\Magento\Framework\Registry $registry,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
		\Tatva\Subscription\Helper\Data $SubscriptionHelper,
		\Tatva\Subscription\Model\Subscription $subscription,
		\Tatva\Catalog\Model\Productdownloadhistorylog $productdownloadhistorylog,
		\Tatva\Deleteaccount\Model\SubscriptionbkpFactory $subscriptionbkp,
		\Tatva\Deleteaccount\Model\DeletedcustomerbkpFactory $deletedcustomerbkp,
		\Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $salesrulecollection,
		\Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
		\Tatva\Subscription\Model\SubscriptionInvitationFactory $subscriptioninvitation
	)
	{
		$this->_deletedcustomerbkp = $deletedcustomerbkp;
		$this->_subscriptionbkp = $subscriptionbkp;
		$this->_productCollection = $productCollection;
		$this->SubscriptionHelper = $SubscriptionHelper;
		$this->_subscription = $subscription;
		$this->_storeManager = $storeManager;
		$this->_dateTime = $dateTime;
		$this->_customer = $customer;
		$this->_transportBuilder = $transportBuilder;
		$this->_scopeConfig = $scopeConfig;
		$this->_registry = $registry;
		$this->_productdownloadhistorylog = $productdownloadhistorylog;
		$this->_customerSession = $customerSession;
		$this->salesrulecollection = $salesrulecollection;
		$this->zohoCRMHelper = $zohoCRMHelper;
		$this->_subscriptioninvitation = $subscriptioninvitation;
	}

	/**
	 * Method is used for disable account via deleting record in customer_entity table
	 * before mailing it and saving the data in backup table
	 */
	public function deleteAccounttBefore($feedback)
	{
		$customer_data = $this->getCustomerData();
		$subscription_details = $this->getSubscriptionDetails();
		$customer_saved_bkp_tbl = $this->saveCustomer2DelTbl($customer_data,$feedback);		
		$customer_subscription_bkp_tbl = $this->saveCustomerSubscriptionTbl($subscription_details);
		$download_data = $this->getDownloadDetails($customer_data['entity_id']);
		//$mailToAdmin_is_sent = $this->sendMailToAdminn($customer_data,$subscription_details,$download_data,$feedback);

		$current_loggedIn_customer = $this->_customerSession->getCustomer();
		$customer_id =  $current_loggedIn_customer->getId();
		$collection = $this->salesrulecollection->create();
        $collection->getSelect()->joinInner(array('src'=>'coupon_customer_relation'),'src.sales_rule_id=main_table.rule_id',array('src.sales_rule_id','src.customer_id'));
        $collection->addFieldToFilter('src.customer_id',$customer_id);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('main_table.rule_id');
        foreach ($collection as $item) 
        {
                $item->delete();
        }

	}

	/**
	 * Method check customer has deleted own account or not
	 * 
	 * @param $t_username : email id of user 
	 * @return $flag_is_deleted true if acc is deleted else false
	 */
	public function accountIsDeleted($t_username)
	{
		$username = $t_username;
		$model = $this->_deletedcustomerbkp;
		$customer = $model->load($username,'email_id');
		$deleted_customer_id = intval($customer->getId());
		$flag_is_deleted = $deleted_customer_id > 0  ? 1 : 0;

		return $flag_is_deleted;
	}


	/**
	 * Method for gathering data about customer 
	 * from customer_entity  table
	 * @return collection of customer's data
	 */

	public function getCustomerData()
	{
		$current_loggedIn_customer = $this->_customerSession->getCustomer();
		$customer_id =  $current_loggedIn_customer->getId();
		$deletedDate = $this->_dateTime->gmtDate();
		$customer_data = array();
		$customer_data = $current_loggedIn_customer->getData();
		$customer_data['deleted_date'] = $deletedDate;

		return $customer_data;
	}


	/**
	 * Method gets subscription details from subscription_history ,productdownload_history , sales_flat_order_grid
	 * tables
	 * @return array of subscription details
	 */
	public function getSubscriptionDetails()
	{
		$current_loggedIn_customer = $this->_customerSession->getCustomer();
		$customer_id =  $current_loggedIn_customer->getId();
		
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

	/**
	 * Method for getting download counts for each subscriptions
	 * @param $customer_id : customer id 
	 * @param $start_date : subscription start date
	 * @param $end_date : subscription end date
	 * @return $download_counts
	 */
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


	/**
	 * Method save data to Deleted Customer's data into <tatva_delacc_customer_bkp> table as backup of deleted customer
	 * @param $customerData
	 * @return $saved_obj for success save in <tatva_delacc_customer_bkp> table
	 */
	public function saveCustomer2DelTbl($customerData,$feedback)
	{
		$data = array(
			'customer_id'=>$customerData['entity_id'],
			'email_id'=>$customerData['email'],
			'firstname'=>$customerData['firstname'],
			'lastname'=>$customerData['lastname'],
			'created_date'=>$customerData['created_at'],
			'deleted_date'=>$customerData['deleted_date'],
			'feedback' =>$feedback
		);
		
		$deletedCustomer_model = $this->_deletedcustomerbkp->create();
		$deletedCustomer_model->setData($data);
		$saved_obj = $deletedCustomer_model->save();

		$field4 = $this->_scopeConfig->getValue('button/emarsys_config/field3',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($field4) {
			$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        	$apiHelper = $_objectManager->get('Tatva\Subscription\Helper\EmarsysHelper');
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


	/**
	 * Method save customer's subscription data into <tatva_delacc_subscription_bkp> table as backup
	 * @param $subscriptionData
	 */
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


	/**
	 * Method sends the mail to Magento Admin as customer deletes account with customer's data
	 * @param $customerData 
	 */
	public function sendMailToAdminn($customerData, $subscription_details, $download_data,$feedback)
	{
		$to = $this->_scopeConfig->getValue('contact/email/recipient_delete_account_to', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$cc = $this->_scopeConfig->getValue('contact/email/recipient_delete_account_cc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$cc_emails = explode(",",$cc);
		$from = 'sales@slideteam.net';
		$fromName = 'SlideTeam Sales';
		$store = $this->_storeManager->getStore()->getId();
		
		$emailTemplateVariables = array();
		$emailTemplateVariables['entity_id'] = $customerData['entity_id'];
		$emailTemplateVariables['firstname'] = ucfirst($customerData['firstname']);
		$emailTemplateVariables['lastname'] = ucfirst($customerData['lastname']);
		$emailTemplateVariables['email'] = $customerData['email'];
		$emailTemplateVariables['created_at'] = $customerData['created_at'];
		$emailTemplateVariables['deletetime'] = $this->_dateTime->gmtDate();
		$emailTemplateVariables['subscription_details'] = $subscription_details;
		$emailTemplateVariables['download_data'] = $download_data;
		$emailTemplateVariables['feedback'] = $feedback;

		try
		{
			$this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_DELETEACCOUNT, ScopeInterface::SCOPE_STORE, $store);
			$this->_transportBuilder->setTemplateIdentifier($this->_template)
			->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
			->setTemplateVars($emailTemplateVariables)
			->setFrom(['name' => $fromName,'email' => $from])
			->addTo($to,"Admin");

			if(!empty($cc_emails))
			{
				foreach($cc_emails as $email)
				{
					$this->_transportBuilder->addCc($email);
				}
			}

			$transport = $this->_transportBuilder->getTransport();
			$transport->sendMessage();
		}
		catch (\Exception $e) 
		{
			echo $e->getMessage();
		}
	}

	/**
	 * Method delete customer's data from db
	 * @return flag for successful delete records from db
	 */
	public function deleteCustomerFromDb($params)
	{
		$flag = true;
		$this->_registry->register('isSecureArea', true);

		$current_loggedIn_customer = $this->_customerSession->getCustomer();
		$customer_id =  $current_loggedIn_customer->getId();
		$this->_customerSession->logout();

		$customer = $this->_customer->load($customer_id);
		$customerEmail = $customer->getEmail();
		$flag = $customer->delete();

		/*child customer delete*/

		$child_customers = $this->SubscriptionHelper->getAllChildSubscriptionHistory($customer_id,$customerEmail);
        if ($child_customers->getSize() > 0 ) {
            foreach ($child_customers as $child) {

            	$invitationmodel = $this->_subscriptioninvitation->create();
            	$invitationmodel->load($child->getInvitationId());
                $invitationmodel->delete();
            	//$childcustomer = $this->_customer->load($child->getChildCustomerId());
                //$flag = $childcustomer->delete();
            }
        }
        
        /*child customer delete*/
		
		return $flag;
	}


	/**
	 * Method returns the payment method used by Customer
	 * @param customer's subscription data
	 * @return payment method Paypal of Two Checkout
	 */
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

	/**
	 * Method return the ids of product downloaded by customer
	 * @param customer id
	 * @return product ids
	 */
	public function getDownloadedProductId($customerId)
	{
		$this->downloadItemCollection = $this->_productdownloadhistorylog->getCollection()
		->addFieldToFilter('customer_id',$customerId)
		->addFieldToSelect('product_id');
		$this->downloadItemCollection->getSelect()->columns('COUNT(product_id) AS no_of_download');
		$this->downloadItemCollection->getSelect()->group('product_id');
		
		return $this->downloadItemCollection;
	}

	/**
	 * Method return details of downloaded products
	 * @param  customer ids
	 * @return downloaded products details(name, url)
	 */
	public function getDownloadDetails($customerId)
	{
		$productsData = array();
		$dataArray = array();
		$productData = $this->getDownloadedProductId($customerId);

		foreach($productData as $key=>$value)
		{
			$productsData[]= $value['product_id'];
		}
		
		$collection = $this->_productCollection->create()
		->addFieldToFilter('entity_id',array("in"=>$productsData))
		->addAttributeToSelect(array('name','url_path'));

		foreach($collection as $product) 
		{
			$dataArray[$product->getEntityId()]['name']=$product->getName();
			$dataArray[$product->getEntityId()]['url']= $product->getProductUrl();
		}      

		return $dataArray;
	}
}
