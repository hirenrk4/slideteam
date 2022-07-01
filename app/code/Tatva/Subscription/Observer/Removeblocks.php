<?php
 
namespace Tatva\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class Removeblocks implements ObserverInterface
{
   protected $customerSession;
   protected $subscription;
   protected $request;
   protected $helper;
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Tatva\Subscription\Model\Subscription $subscription,
        \Magento\Framework\App\Request\Http $request,
        \Tatva\Subscription\Helper\TeamPlans $helper
    ) {
        $this->_customerSession = $customerSession;
        $this->subscription = $subscription;
        $this->request = $request;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
      //subscription_history
      $actionName = $this->request->getFullActionName();
      $allowedUrls = array('subscription_index_list','subscription_index_childlist','customer_account_index','customer_account_edit','wishlist_index_index','downloadscount_index_index');
      
      if (in_array($actionName, $allowedUrls)) {
         $subscription_history = $this->subscription->getLastPaidSubscriptionhistory();
         $subscription_plan = "";
         $showChildInvitation = 0;
         if (is_object($subscription_history)){
             $subscription_plan = $subscription_history->getSubscriptionPeriod();
         }
         $team_plan_array = $this->helper->getTeamSubscriptionPlans();
         if(in_array($subscription_plan,$team_plan_array) && empty($subscription_history->getParentCustomerId()))
         {
           $showChildInvitation = 1;
         }

         if ($showChildInvitation != 1) {
           $layout = $observer->getLayout();
           $layout->unsetElement('customer-account-navigation-my-childuser');
         }
      }
       
    }
}