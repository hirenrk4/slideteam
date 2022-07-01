<?php

namespace Tatva\Customer\Controller\Account;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

class EditPost extends \Magento\Customer\Controller\Account\EditPost {

    private $customerMapper;
    
     /**
     * $EmarsysHelper
     *
     * @type \Tatva\Emarsys\Helper\Data
     */
    protected $EmarsysHelper;
    /**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;
    protected $_redirect;
    protected $_customerFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context, 
            \Magento\Customer\Model\Session $customerSession, 
            \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository, 
            \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, 
            \Magento\Customer\Model\CustomerExtractor $customerExtractor,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
            \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
            \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
            \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
            \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
            \Magento\Customer\Model\CustomerFactory $customerFactory
  ) {
         $this->_scopeConfig = $scopeConfig;
         $this->_httpClientFactory   = $httpClientFactory;
         $this->EmarsysHelper = $EmarsysHelper;
         $this->emarsysApiHelper = $emarsysApiHelper;
         $this->zohoCRMHelper = $zohoCRMHelper;
         $this->_redirect = $redirectInterface;
         $this->_customerFactory = $customerFactory;
        parent::__construct($context, $customerSession, $customerAccountManagement, $customerRepository, $formKeyValidator, $customerExtractor);
    }
    
    public function execute() {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost()) {
            $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                    $this->_request, $currentCustomerDataObject
            );

            try {
                
                // whether a customer enabled change password option
                $isPasswordChanged = $this->changeCustomerPassword($currentCustomerDataObject->getEmail());

                $telephone = $this->_request->getParam('contact_number');
                $isd_code = $this->_request->getParam('isd-code');
                $country_code = $this->_request->getParam('country_id');
                $contact_number ='';
                if(isset($telephone) && !empty($telephone)){
                    $contact_number = '+'.$isd_code.' '.$telephone;
                }
                
                $this->customerRepository->save($customerCandidateDataObject);

                $customerId = $customerCandidateDataObject->getId();
                $custom = $this->_customerFactory ->create();
                $customer = $custom->load($customerId)->getDataModel();
                $customer->setCustomAttribute('contact_number',$contact_number);
                if(isset($country_code) && !empty($country_code)){
                    $customer->setCustomAttribute('customer_country_code',$country_code);
                }
                else{
                    $customer->setCustomAttribute('customer_country_code',null);
                }
                $custom->updateData($customer);
                $custom->save();

                //Emarsys Integration

                $field4 =  $this->EmarsysHelper->isApiEnabled();
              if($field4)
                {
                   //$url = $this->EmarsysHelper->getApiUrl();

                    $contact = array(
                                    "1"=>$customerCandidateDataObject->getFirstname(),
                                    "2"=>$customerCandidateDataObject->getLastname(),
                                    "3"=>$customerCandidateDataObject->getEmail(),
                                    "485"=>$customerCandidateDataObject->getId()
                                  );
                    $encode = json_encode($contact);
                    
                    try {
                        
                        $apiHelper = $this->emarsysApiHelper;
                        $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');

                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
                 //Emarsys Integration
                //Zoho CRM integration start//
                if($this->zohoCRMHelper->isEnabled()){
                    $url = $this->_redirect->getRefererUrl();
                    $customerEditData =array(
                        "First_Name"=>$customerCandidateDataObject->getFirstname(),
                        "Last_Name"=>$customerCandidateDataObject->getLastname(),
                        "Email"=>$customerCandidateDataObject->getEmail(),
                        "Priority"=>"2",
                        "URL"=>$url
                    );
                    if(isset($telephone) && !empty($telephone)){
                        $customerEditData["Comment"]="Edit Customer";
                        $customerEditData["Phone"]=$telephone;
                        $customerEditData["Country"] = $this->zohoCRMHelper->getCountryname($country_code);
                        $customerEditData["Customer_Action"]="None";
                    }else{
                        $customerEditData["Comment"]="Edit Customer And Remove Contact Detail";
                        $customerEditData["Customer_Action"]="Contact Deleted";
                    }
                    $this->zohoCRMHelper->editCustomer($customerEditData,$customerCandidateDataObject->getId());
                }
                //Zoho CRM integration end//
                $this->dispatchSuccessEvent($customerCandidateDataObject);
                $this->messageManager->addSuccess(__('The account information has been saved.'));
                return $resultRedirect->setPath('customer/account');
            } catch (InvalidEmailOrPasswordException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (UserLockedException $e) {
                $message = __(
                        'Invalid login or password.'
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addError($message);
                return $resultRedirect->setPath('customer/account/login');
            } catch (InputException $e) {
                $this->messageManager->addError($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }

            $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }

        return $resultRedirect->setPath('*/*/edit');
    }
    
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }
    
    private function populateNewCustomerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }
    
    private function getCustomerMapper()
    {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get(\Magento\Customer\Model\Customer\Mapper::class);
        }
        return $this->customerMapper;
    }
    
    /**
     * Account editing action completed successfully event
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject
     * @return void
     */
    private function dispatchSuccessEvent(\Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject)
    {
        $this->_eventManager->dispatch(
            'customer_account_edited',
            ['email' => $customerCandidateDataObject->getEmail()]
        );
    }
}