<?php

namespace Tatva\Subscription\Controller\Index;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Tatva\Subscription\Model\SubscriptionInvitationFactory;

class Customerinvite extends \Magento\Framework\App\Action\Action
{
  protected $EmarsysHelper;
  protected $resultPageFactory = false;
  protected $_messageManager;
  protected $customerRepository;
  protected $encryptor;
  protected $SubscriptionInvitation;
  protected $_customerSession;

  public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory,
      \Tatva\Subscription\Model\SubscriptionInvitationFactory  $subscriptioninvitation,
      \Tatva\Subscription\Model\SubscriptionFactory $subscriptionRepostry,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Customer\Model\Customer $customerModel,
      CustomerRepositoryInterface $customerRepository,
      \Magento\Framework\Stdlib\DateTime\DateTime $date,
      \Tatva\Subscription\Helper\Data $subscriptionhelper,
      \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
      \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
      \Magento\Framework\Encryption\EncryptorInterface $encryptor,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      StateInterface $inlineTranslation,
      TransportBuilder $transportBuilder,
      \Magento\Framework\Message\ManagerInterface $messageManager,
      \Magento\Customer\Model\Session $customerSession,
      \Tatva\Subscription\Helper\TeamPlans $teamplanModel
  )
  {
    parent::__construct($context);
      $this->resultPageFactory = $resultPageFactory;
      $this->_subscriptioninvitation = $subscriptioninvitation;
      $this->__subscriptionRepostry = $subscriptionRepostry;
      $this->scopeConfig = $scopeConfig;
      $this->__customerModel = $customerModel;
      $this->customerRepository = $customerRepository;
      $this->date = $date;
      $this->subscriptionhelper = $subscriptionhelper;
      $this->EmarsysHelper = $EmarsysHelper;
      $this->emarsysApiHelper = $emarsysApiHelper;
      $this->encryptor = $encryptor;
      $this->_storeManager = $storeManager;
      $this->_inlineTranslation = $inlineTranslation;
      $this->_transportBuilder = $transportBuilder;
      $this->messageManager = $messageManager;
      $this->_customerSession = $customerSession;
      $this->_teamplanModel = $teamplanModel;
      $this->date = $date;
  }

  public function execute()
  { 
    $post = (array) $this->getRequest()->getPost();
    $emails = $post['customer_email'];
    $websiteId = 1;
    $currentCustomer = $this->_customerSession->getCustomer();
    $parent_Subscription = $this->subscriptionhelper->getParentSubscriptionHistory($currentCustomer->getId());
    $childSubscriptionCount = $this->subscriptionhelper->getSubscriptionRemaining($currentCustomer->getId());
    $post['to_date'] = $parent_Subscription->getToDate();
    $post['from_date'] = $parent_Subscription->getFromDate();

    $subscription_plan = $parent_Subscription->getSubscriptionPeriod();
    $team_plan_array = $this->_teamplanModel->getTeamSubscriptionPlans();

    if(!in_array($subscription_plan,$team_plan_array) || !empty($parent_Subscription->getParentCustomerId()))
    {
      $this->messageManager->addError('Sorry you are not able to add other customers.');
      $this->_redirect('subscription/index/childlist'); 
    }

    if(isset($post['to_date']))
    {
      $to_date=date("Y-m-d",strtotime($post['to_date']));
      $to_date_strtotime=strtotime($to_date);
      if($to_date_strtotime)
      {
        $from_date=date("Y-m-d",strtotime($post['from_date']));
        $from_date_strtotime=strtotime($from_date);
        $date = date("Y-m-d",strtotime($this->date->gmtDate()));

        if(strtotime($to_date) < strtotime($date))
        {
          $this->messageManager->addError(__('End Date must be greater or equal to todays date.'));

          $this->_redirect('subscription/index/childlist');
          return;
        }
        elseif($from_date_strtotime>$to_date_strtotime)
        {
          $this->messageManager->addError(__('Start Date must be less than or equal to End Date.'));
          $this->_redirect('subscription/index/childlist');

          return;
        }
        $post['renew_date'] = date("Y-m-d",strtotime($post['to_date'] . "+ 1 day"));
        $post['admin_modified']=0;
      }
      else
      {
        $this->messageManager->addError(__('Invalid End Date.'));
        $this->_redirect('subscription/index/childlist');

        return;
      }
    }
  
    $finalreturnParam = false;
    foreach ($emails as $email) {
      list($userlimit,$remaining_limit,$no_of_users) = $this->_teamplanModel->getPlanLimit($subscription_plan,$childSubscriptionCount,$currentCustomer->getId());
      if($remaining_limit > 0)
      {
        $returnParam = $this->customerExists($email,$websiteId,$currentCustomer,$parent_Subscription,$post);
        if(!empty($returnParam))
        {
          $finalreturnParam = true; 
        } 
      }
    }
  
    if(!empty($finalreturnParam))
    {
      $this->messageManager->addSuccessMessage('Invitation successfully sent to the user. Once they register, their account will have the same access rights as yours.');
      $this->_redirect('subscription/index/childlist'); 
    }
    else
    {
      $this->messageManager->addErrorMessage('This user has been already subscribed. Or Someone already request to this customer.'); 
      $this->_redirect('subscription/index/childlist'); 
    }
  }

  public function customerExists($email, $websiteId = 1,$currentCustomer,$parentCustomerSubscription,$post)
  {
    $post['parent_customer'] = $post['parent_customer_id'] = $currentCustomer->getId();

    $customer = $this->__customerModel;
    if ($websiteId) {
        $customer->setWebsiteId($websiteId);
    }
    
    $customer->loadByEmail($email);

    if($customer->getId()) 
    {
      $lastsubscription = $this->subscriptionhelper->getSubscriptionHistory($customer->getId());
      
      foreach($lastsubscription as $subscription_exist)
      {
        $subPeriod = $subscription_exist->getSubscriptionPeriod();
        $parentPeriod = $parentCustomerSubscription->getSubscriptionPeriod();
        $childParentId = $subscription_exist->getParentCustomerId();
        $ParentCustomerId = $parentCustomerSubscription->getCustomerId();
        $downloadLimit = $subscription_exist->getDownloadLimit();
        $status = $subscription_exist->getStatusSuccess();

        if($childParentId == $ParentCustomerId && $subPeriod == $parentPeriod && $downloadLimit == 0)
        {
          $subscription_exist->setDownloadLimit($parentCustomerSubscription->getDownloadLimit())
          ->setStatusSuccess("Paid")
          ->setFromDate($parentCustomerSubscription->getFromDate())
          ->setToDate($parentCustomerSubscription->getToDate())
          ->setIncrementId($parentCustomerSubscription->getIncrementId())
          ->save();

          $invitationmodel = $this->_subscriptioninvitation->create();
          $invitationmodel->addData([
            "parent_customer" => $post['parent_customer'],
            "customer_email" => $email,
            "child_customer_id" => $customer->getId()
            ]);
          $saveData = $invitationmodel->save();

          //$this->messageManager->addSuccessMessage('Invitation successfully sent to the user. Once they register, their account will have the same access rights as yours.');
          //$this->_redirect('subscription/index/childlist');
          return true;
        }
        elseif($childParentId != $ParentCustomerId && $status == "Paid")
        {
          //$this->messageManager->addErrorMessage('This user has been already subscribed.'); 
          //$this->_redirect('subscription/index/childlist');
          return false;
        }
        elseif($childParentId == $ParentCustomerId && $subPeriod == $parentPeriod && $status == "Paid")
        {
          //$this->messageManager->addErrorMessage('This user has been already subscribed.'); 
          //$this->_redirect('subscription/index/childlist');
          return false;
        }
      }
        
      $post['customer_id'] = $customer->getId();
      $post['subscription_period'] = $parentCustomerSubscription->getSubscriptionPeriod();
      $post['to_date'] = $parentCustomerSubscription->getToDate();
      $post['download_limit'] = $parentCustomerSubscription->getDownloadLimit();
      $post['from_date'] = $parentCustomerSubscription->getFromDate();
      $post['increment_id'] = $parentCustomerSubscription->getIncrementId();
      return $this->save($post,$email);
    }
    else 
    {
      return $this->sendMail($post,$email); 
    }
  }

  public function save($post,$email) {
    if ($post) 
    {
      $customerData = $this->customerRepository->get($email);
      $customerId = (int)$customerData->getId();
      $period_full = $post['subscription_period'];
      $post['parent_customer_id']=$post['parent_customer']; 
      $post['customer_id']=$customerId;
      $customer_id = $customerId;
      $subscription_expiry_date = $post['to_date'];
      $download_limit = $post['download_limit'];
      $to_date=date("Y-m-d",strtotime($post['to_date']));
      $customer_type = 1;
      $requestData=$this->getRequest()->getParam('addSubscription');
      if($download_limit==0):$customer_type=2;endif;
      
      $invitationmodel = $this->_subscriptioninvitation->create();
      $invitation_sent = $this->subscriptionhelper->getInvitationEmail($email);
      if ($invitation_sent['customer_email'] != $email) {
        $invitationmodel->addData([
          "parent_customer" => $post['parent_customer'],
          "customer_email" => $email,
          "child_customer_id" => $customerId
          ]);
        $saveData = $invitationmodel->save();
      }
    else
    {
      return false;
    } 

      try {

        if ($invitation_sent['customer_email'] != $email) {

          $model = $this->__subscriptionRepostry->create();
          $model->setData($post);
          $model->save();

        }
        /*send confirmation mail*/
        $sender_customer = $this->customerRepository->getById($post['parent_customer']);
        $rec_customer = $this->customerRepository->getById($customerId);
        
        $template = 'subscription_confirmation';
        $recipient_email = $email;
        $recipient_name = $rec_customer->getFirstName();
        $sender = [
          'name' => "Slideteam support",
          'email' => 'support@slideteam.net'
        ];
        //an array with variables, format is key = variable name, value = variable value
        $vars = [
            'parent_name' => ucfirst($sender_customer->getFirstName()),
            'parent_email'    => $sender_customer->getEmail(),
            'child_email'    => $recipient_email
        ];
        //several variables in email template, i.e. storeName are generated based on store Id
        $storeId = 1;
       
        $this->_inlineTranslation->suspend();
            $this->_transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )->setTemplateVars(
                $vars
            )->setFrom(
                $sender
            )->addTo(
                $recipient_email,
                $recipient_name
            );
        
        //create the transport
        if (!isset($transport)) {
            $transport = $this->_transportBuilder->getTransport();
        }
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
        /*send confirmation mail end*/
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
        
        return true;
      
      }
      catch (Exception $e) {
        //$this->messageManager->addError(__($e->getMessage()));
        return false;
      }
    }
    return false;
  }

  public function sendMail($post,$email)
  {
    $customerData = $this->customerRepository->getById($post['parent_customer']);
    $customerId = (int)$customerData->getId();
    $data = $post['parent_customer'] ." ". $email;
    $encrypted =  $this->encryptor->encrypt($data);
    //define variables for the email generate email template with template file and templates variables 

    $invitation_sent = $this->subscriptionhelper->getInvitationEmail($email);
    if ($invitation_sent['customer_email'] != $email) {
      $model = $this->_subscriptioninvitation->create();
      $model->addData([
        "parent_customer" => $post['parent_customer'],
        "customer_email" => $email,
        "encrypted_key" => $encrypted
        ]);
      $saveData = $model->save();
    }
    else
    {
      return false;
    }
    
    $template = 'subscription_newcustomer_register';
    $recipient_email = $email;
    $recipient_name = $email;
    $sender = [
      'name' => "Slideteam support",
      'email' => 'support@slideteam.net'
    ];
    //an array with variables, format is key = variable name, value = variable value
    $vars = [
        'parent_name' => ucfirst($customerData->getFirstName()),
        'parent_email'    => $customerData->getEmail(),
        'child_email'    => $recipient_email
    ];
    //several variables in email template, i.e. storeName are generated based on store Id
    $storeId = 1;
   
    $this->_inlineTranslation->suspend();
    //create email template for Magento 2.2.x and 2.3.x
    
        $this->_transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ]
        )->setTemplateVars(
            $vars
        )->setFrom(
            $sender
        )->addTo(
            $recipient_email,
            $recipient_name
        );
    
    //create the transport
    if (!isset($transport)) {
        $transport = $this->_transportBuilder->getTransport();
    }
    
    //send the email
    try {
        $transport->sendMessage();
        //$this->messageManager->addSuccessMessage('Invitation successfully sent to the user. Once they register, their account will have the same access rights as yours.');
        //$this->_redirect('subscription/index/childlist');
        $this->_inlineTranslation->resume();
        return true;
    } catch (\Exception $exception) {
      return false;
    }
  }
}