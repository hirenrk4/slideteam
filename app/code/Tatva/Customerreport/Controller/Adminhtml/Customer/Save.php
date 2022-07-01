<?php
namespace Tatva\Customerreport\Controller\Adminhtml\Customer;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Framework\App\Action\Action
{

   /**
   * $EmarsysHelper
   *
   * @type \Tatva\Emarsys\Helper\Data
   */
   protected $EmarsysHelper;
   protected $resultPageFactory = false;
   public function __construct(
   	\Magento\Backend\App\Action\Context $context,
   	\Magento\Framework\View\Result\PageFactory $resultPageFactory,
   	\Tatva\Subscription\Model\SubscriptionFactory $subscriptionRepostry,
   	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
   	\Magento\Customer\Model\Customer $customerModel,
   	\Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
   	\Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
      \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper
   ) {
   	parent::__construct($context);
   	$this->resultPageFactory = $resultPageFactory;
   	$this->__subscriptionRepostry = $subscriptionRepostry;
   	$this->scopeConfig = $scopeConfig;
   	$this->__customerModel = $customerModel;
   	$this->_dateFactory = $dateFactory; 
   	$this->EmarsysHelper = $EmarsysHelper;
      $this->emarsysApiHelper = $emarsysApiHelper;
   }
   public function execute() 
   {
   	$post = (array) $this->getRequest()->getPost('addSubscription');
   	$this->save($post);
   }

   public function save($post) {
   	if ($post) {
   		$period_full = $post['subscription_period'];
   		$post['customer_id']=$post['entity_id'];
   		
   		if(!empty($post['parent_customer_id'])) :
         	$post['parent_customer_id ']=$post['parent_customer_id'];
         endif;

   		$customer_id = $post['entity_id'];
   		$subscription_expiry_date = $post['to_date'];
   		$download_limit = $post['download_limit'];
   		$customer_type = 1;
   		$requestData=$this->getRequest()->getParam('addSubscription');
   		if($download_limit==0):$customer_type=2;endif;
   		if(is_array($post) && isset($post['to_date']))
   		{
   			$to_date=date("Y-m-d",strtotime($post['to_date']));
   			$to_date_strtotime=strtotime($to_date);
   			if($to_date_strtotime)
   			{
   				$from_date=date("Y-m-d",strtotime($post['from_date']));
   				$from_date_strtotime=strtotime($from_date);

   				if($to_date<date("Y-m-d"))
   				{
   					$this->messageManager->addError(__('End Date must be greater or equal to todays date.'));

   					$this->_redirect('customerreport/customer/edit', array('entity_id' => $requestData['entity_id']));
   					return;
   				}
   				else if($from_date_strtotime>$to_date_strtotime)
   				{
   					$this->messageManager->addError(__('Start Date must be less than or equal to End Date.'));
   					$this->_redirect('customerreport/customer/edit', array('entity_id' => $requestData['entity_id']));

   					return;
   				}
   				$post['renew_date'] = date("Y-m-d",strtotime($post['to_date'] . "+ 1 day"));
   				$post['admin_modified']=1;
   			}
   			else
   			{
   				$this->messageManager->addError(__('Invalid End Date.'));
   				$this->_redirect('customerreport/customer/edit', array('entity_id' => $requestData['entity_id']));

   				return;
   			}
   		}

   		$model = $this->__subscriptionRepostry->create();
   		$model->setData($post);

   		// $subscription_model = $model->getCollection();
   		// $subscription_model->addFieldToFilter('customer_id',$post['entity_id']);
   		// $subscription_model->addFieldToSelect(array('created_time','update_time'));
   		// $created_date = null;
   		// $update_date = null;
   		// foreach($subscription_model as $data)
   		// {
   		// 	$created_date = $data['created_time'];
   		// 	$update_date = $data['update_time'];
   		// }

   		try {

				//$date = $this->_dateFactory->create()->gmtDate(); // instead of now function
				
			 	$model->save();
            
            //Emarsys integration start//
			 	$field4 =  $this->EmarsysHelper->isApiEnabled();
			 	if($field4)
			 	{
			 		$subscription_duration = null;
			 		switch($period_full)
			 		{
			 			case($period_full=="Monthly") : $subscription_duration = 1;break;
			 			case($period_full=="Semi-annual") : $subscription_duration = 2;break;
			 			case($period_full=="Annual") : $subscription_duration = 3;break;
			 			case($period_full=="Annual + Custom Design") : $subscription_duration = 4;break;
			 			case((stripos($period_full,'enterprise') !== false)) : $subscription_duration = 5;break;
			 			case((stripos($period_full,'Monthly +') !== false)) : $subscription_duration = 6;break;
			 			case((stripos($period_full,'Semi-annual +') !== false)) : $subscription_duration = 7;break;
                  case($period_full=="Annual 4 User License") : $subscription_duration = 11;break;
                  case($period_full=="Annual 20 User License") : $subscription_duration = 12;break;
                  case($period_full=="Annual Company Wide Unlimited User License") : $subscription_duration = 13;break;
                  case($period_full=="Annual 15 User Education License") : $subscription_duration = 14;break;
                  case($period_full=="Annual UNLIMITED User Institute Wide License") : $subscription_duration = 15;break;
			 		}

			 		$customerData = $this->__customerModel->load($customer_id);
			 		$fields_string = "";
			 		
               $contact = array(
                             "1"=>$customerData->getFirstname(),
                             "2"=>$customerData->getLastname(),
                             "3"=>$customerData->getEmail(),
                             "485"=>$customer_id,
                             "488"=>$to_date,
                             "489"=>$subscription_duration,
                             "490"=>$customer_type
                           );
               $encode = json_encode($contact); 
               
               $apiHelper = $this->emarsysApiHelper;
               $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
			 	}
				//Emarsys integration end//
				$this->messageManager->addSuccessMessage('Subscription was successfully saved');
			 	if ($this->getRequest()->getParam('back')) {
			 		$this->_redirect('customerreport/customer/edit', array('entity_id' => $model->getEntityId()));
			 		return;
			 	}
			 	$this->_redirect('customerreport/customer/reports');
				return;
				 
			}catch (Exception $e) {
			 	$this->messageManager->addError(__($e->getMessage()));
			 	$this->_redirect('customerreport/customer/edit', array('entity_id' => $requestData['entity_id']));
			 	return;
			}
		}
		$this->messageManager->addError__('Unable to find subscription to save');
		$this->_redirect('*/*/');
	}
}
