<?php
namespace Tatva\PaidCustomerPopup\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLoggedin implements ObserverInterface
{
    protected $customerSession;
    protected $subscriptionModel;
    protected $customerFactory;

    public function __construct
    (
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Tatva\Subscription\Model\Subscription $subscriptionModel
    ) 
    {
        $this->customerSession = $customerSession;
        $this->subscriptionModel = $subscriptionModel;
        $this->customerFactory = $customerFactory;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerId = $this->customerSession->getCustomerId();
        if($this->check_user_type($customerId)){
            $custom = $this->customerFactory ->create();
            $customer = $custom->load($customerId)->getDataModel();
            $secondLogin = $customer->getCustomAttribute('second_login');
            if(isset($secondLogin)){
                $secondLoginValue = $secondLogin->getValue(); 
                if($secondLoginValue <=1){
                  $customer->setCustomAttribute('second_login',++$secondLoginValue);
                  $custom->updateData($customer);
                  $custom->save();
                }
            }else{
                $customer->setCustomAttribute('second_login',1);
                $custom->updateData($customer);
                $custom->save();
            }
            $this->customerSession->setIsPaidCurrentLogin(1);
        }else{
            $this->customerSession->setIsPaidCurrentLogin(0);
        }
        return $this;
    }

    public function check_user_type($customer_id) {
        $customer_type_flag = false;
        $customer_type_array = $this->subscriptionModel->getCustomerType($customer_id);

        if(is_array($customer_type_array)) {
            $customerSubscription = $customer_type_array['customerType'];
        }
        else {
            $customerSubscription = $customer_type_array;
        }
        if($customerSubscription == "Active" || $customerSubscription == "Expired"){
            $customer_type_flag = true;
        }
        
        return $customer_type_flag;
    }
}