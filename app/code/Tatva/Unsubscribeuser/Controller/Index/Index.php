<?php

namespace Tatva\Unsubscribeuser\Controller\Index;
use Tatva\Unsubscribeuser\Model\UnsubscribeFactory;

use Zend\Log\Filter\Timestamp;
use Magento\Store\Model\StoreManagerInterface;
class Index extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;
    protected $_unsubscribeFactory;
    protected $_messageManager;
    protected $date;

     
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_logLoggerInterface;
    protected $storeManager;
    protected $_customers;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        UnsubscribeFactory $UnsubscribeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customers,
        \Tatva\Subscription\Model\SubscriptionFactory $subscription,
        array $data = []
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_unsubscribeFactory = $UnsubscribeFactory;
        $this->_messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->date = $date;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->messageManager = $context->getMessageManager();
        $this->storeManager = $storeManager;
        $this->_customers = $customers;
        $this->_subscription = $subscription;
        parent::__construct($context);
    }

	public function execute()
    {
    	
        $data = $this->getRequest()->getPost();
        $delayedTime = $this->_scopeConfig->getValue('unsubscription_options/unsubscription/unsubscription_delay_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	try {
    		$dataNew = array();
			$dataNew['customer_id'] = $data['customer_id'];
			$dataNew['subscription_id'] = $data['subscription_id'];
			$dataNew['unsubscribe_date'] = $this->date->gmtDate();
			// $dataNew['type_of_paymethod'] = $data['type_of_paymethod'];
			
			if ($data['feedback'] == 'option1')
			{
				$dataNew['reason'] = 'I just wanted to download free products.';
			}
			elseif ($data['feedback'] == 'option2')
			{
				$dataNew['reason'] = 'I need more industry specific design. & Industry Name = ' .$data['industry'];
			}
			elseif ($data['feedback'] == 'option3')
			{
				$dataNew['reason'] = 'You don\'t have the designs I am looking for.';
			}
			elseif($data['feedback'] == 'option4')
			{
				$dataNew['reason'] = 'Price is too high.';
			}
			else{
				$dataNew['reason'] = 'Other comment : '.$data['comment'];
			}
			
			$dataNew['status'] = $data['status'];
    	// print_r($dataNew);die;


			$model = $this->_unsubscribeFactory->create();
			//$resultPageData = $model->load('*');
			$collection = $model->getCollection()->addFieldToFilter('customer_id', array('eq' => $data['customer_id']))->addFieldToFilter('subscription_id',array('eq'=>$data['subscription_id']))->addFieldToFilter('status',array('eq'=>'pending'));

	    	$customer = $this->_customers->load($data['customer_id']);
		    $customerName = $customer->getFirstname();
		    $customerEmail = $customer->getEmail();

			if ($collection->count() == 0) {

			    if (isset($data['id'])) {
			        $model->load($dataNew['id']);
			    }

			    $model->setData($dataNew);
			    $model->save();

			    $incrid = $model->getId();
			    
			    $this->_subscription->create()->load($data['subscription_id'])->setUnsubscribeOrder($incrid)->save();

			    // mail code


			    	try
			    	{
			    	    $this->_inlineTranslation->suspend();
			    	    $senderEmail = $this->_scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);     
			    	    $sender = [
			    	        'name' => 'Slideteam',
			    	        'email' => $senderEmail
			    	    ];
			    	     
			    	    $sentToEmail = $customerEmail;
			    	    $sentToName = $customerName;
			    	     
			    	     
			    	    $transport = $this->_transportBuilder
			    	    ->setTemplateIdentifier('unsubscribeuser_email_template')
			    	    ->setTemplateOptions(
			    	        [
			    	            'area' => 'frontend',
			    	            'store' => $this->storeManager->getStore()->getId()
			    	        ]
			    	        )
			    	    ->setTemplateVars([
			    	            'name'  => $customerName,
			    	            'message1' => 'Your Unsubscribe request successfully accepted.',
			    	            'message2' => 'Your plan will be unsubscribe after '.$delayedTime.' Hours'
			    	        ])
			    	    ->setFrom($sender)
			    	    ->addTo($sentToEmail,$sentToName)
			    	        //->addTo('owner@example.com','owner')
			    	    ->getTransport();
			    	         
			    	     $transport->sendMessage();

			    	    // Mail Send To Admin Code //

			    	     $transport = $this->_transportBuilder
			    	    ->setTemplateIdentifier('unsubscribeuser_email_template_admin')
			    	    ->setTemplateOptions(
			    	        [
			    	            'area' => 'frontend',
			    	            'store' => $this->storeManager->getStore()->getId()
			    	        ]
			    	        )
			    	    ->setTemplateVars([
			    	            'name'  => $customerName,
			    	            'c_email'  => $customerEmail,
			    	            'message1' => 'Customer requested for the unsubscription. Please review it as soon as possible.',
			    	            
			    	        ])
			    	    ->setFrom($sender)
			    	    ->addTo("geetika.gosain@slideteam.net","geetika")
			    	    ->addCc("ron@slideteam.net","rahul")
			    	    ->getTransport();
			    	         
			    	     $transport->sendMessage();

			    	     // Mail Send To Admin Code //
			    	         
			    	    $this->_inlineTranslation->resume();
			    	    $this->_messageManager->addSuccess(__("Unsubscribe Request successfully sent to Admin,The account will be unsubscribe in ".$delayedTime." hrs."));
			    	         
			    	} catch(\Exception $e){
			    	    $this->messageManager->addError($e->getMessage());
			    	    $this->_logLoggerInterface->debug($e->getMessage());
			    	    exit;
			    	}



			}else{
				$this->_messageManager->addNotice(__('You have already requested for Unsubscribe.'));
			}
		    $resultRedirect = $this->resultRedirectFactory->create();
		    $resultRedirect->setPath('subscription/index/list/');
		    return $resultRedirect;

	    } catch (\Exception $e) {
	        $this->$e->getMessage();
	    }
    }


}