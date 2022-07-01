<?php
namespace Tatva\Loginpopup\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;

class save extends \Magento\Framework\App\Action\Action
{
    protected $_customer;
    protected $_customerSession;
    protected $_customerFactory;
    protected $_messageManager;
    protected $customeradditionalModel;
    protected $subscriptionModel;
    protected $_scopeConfig;
    protected $_transportBuilder;
    protected $_storeManager;


    const XML_PATH_EMAIL_LOGINPOPUP = 'tatva/loginpopup/email';

    public function __construct(
        Context $context, 
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Tatva\Loginpopup\Model\CustomerAdditionlData $customeradditionalModel,
        \Tatva\Subscription\Model\Subscription $subscriptionModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Tatva\Portfolio\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_customer = $customer;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->customeradditionalModel = $customeradditionalModel;
        $this->_messageManager = $messageManager;
        $this->subscriptionModel = $subscriptionModel;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $previous_full_url = $this->_redirect->getRefererUrl();
        $params = $this->getRequest()->getParams();

        $industry = $params['industry'];
        $job_profile = $params['job_profile'];

        //Customer Information
        $customer_data = $this->_customerSession->getCustomer();
        $customer_id = $customer_data->getId(); 
        $customer_name = $customer_data->getName();
        $customer_email = $customer_data->getEmail();

        
        $customer = $this->_customer->load($this->_customerSession->getCustomer()->getId());
        $customerData = $customer->getDataModel();
        $customerData->setCustomAttribute('industry_attribute',$industry);
        $customerData->setCustomAttribute('job_profile_attribute',$job_profile);
        $customer->updateData($customerData);

        //Subscription Data
        $subscription = $this->subscriptionModel->getCustomerType($customer_id);

        $exist_customer = $this->customeradditionalModel->getCollection()->addFieldToFilter("customer_id",$customer_id)->addFieldToSelect("id");
        $exCustomerData = $exist_customer->getData();

        $this->customeradditionalModel->setCustomerId($customer_id);
        if(empty($exCustomerData))
        {           
            foreach($params as $key => $val) {
                $this->customeradditionalModel->setData($key, $val);
            }
            $this->customeradditionalModel->setCreatedTime(time())
                                            ->setsetUpdateTime(time());
            
            $this->customeradditionalModel->save();
        }
        else
        {
            foreach($exist_customer as $data){
                $exist_customer_id = $data->getId();
            }
            $this->customeradditionalModel->setUpdateTime(time());         
            $this->customeradditionalModel->load($exist_customer_id)->addData($params)->save();
        }

        $customerResource = $this->_customerFactory->create();

        if ($industry != "") 
        {
            $customerResource->saveAttribute($customer, 'industry_attribute');
        }

        if ($job_profile != "") 
        {
            $customerResource->saveAttribute($customer, 'job_profile_attribute');
        }

        if((boolean)substr_count($this->_redirect->getRefererUrl(),'Edit'))
        {
            $this->_messageManager->addSuccess('You saved the Additional information.');
            $this->_redirect('customer/account/');
        }
        //Email functionality
        if(is_array($subscription))
        {
            $customerType = $subscription['customerType'] == 'Active' ? 'Paid' : 'Expired';
            $subscriptionData = $subscription['latestSubscription'];
        }else{
            $customerType = 'Free';
        }

        $toEnable = $this->_scopeConfig->getValue('contact/email/enable_email_loginpopup_to');
        
        if(!empty($toEnable))
        {
            $store = $this->_storeManager->getStore()->getId();
            $emailTemplateVariables = array();
            $emailTemplateVariables = $this->getRequest()->getParams();
            $emailTemplateVariables['customerID'] = $this->_customerSession->getCustomer()->getId();
            $emailTemplateVariables['customerName'] = $this->_customerSession->getCustomer()->getName();
            $emailTemplateVariables['customerEmail'] = $this->_customerSession->getCustomer()->getEmail();
            $emailTemplateVariables['customerType'] = $customerType;
            if($customerType == "Active"){ $emailTemplateVariables['subscriptionData'] = $subscriptionData; }
            $emailTemplateVariables['emailSubject'] = "Customer Survay Data of ";


            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            $to = $this->_scopeConfig->getValue('contact/email/recipient_email_loginpopup');
            $bcc = explode(',', $this->_scopeConfig->getValue('contact/email/recipient_email_loginpopup_bcc'));

            try {
                $this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_LOGINPOPUP, ScopeInterface::SCOPE_STORE, $store);
                $this->_transportBuilder->setTemplateIdentifier($this->_template)
                            ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
                            ->setTemplateVars($emailTemplateVariables)
                            ->setFrom(['name' => 'Slideteam','email' => 'design@slideteam.net'])
                            ->addTo($to, "Admin");
                $bccEnable = $this->_scopeConfig->getValue('contact/email/enable_email_loginpopup_bcc');
                if(!empty($bccEnable))
                {
                    foreach($bcc as $email)
                        {
                            $this->_transportBuilder->addBcc($email); 
                        }
                               
                }

                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (Exception $ex) {
                    
                echo $e->getMessage();
                $this->_messageManager->addError("Unable to send email");
                $this->_redirect($previous_full_url);
            }
        }

    }
}
