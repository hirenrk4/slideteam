<?php

namespace Tatva\Subscription\Helper;
use Magento\Framework\App\Helper\AbstractHelper;


class TeamPlans extends AbstractHelper
{
  
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Tatva\Subscription\Helper\Data $SubscriptionHelper,
        \Tatva\Subscription\Model\SubscriptionFactory $subscription,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) 

    {
        $this->SubscriptionHelper = $SubscriptionHelper;
        $this->_subscription = $subscription;
        $this->date = $date;
        parent::__construct($context);
    }

    public function getTeamSubscriptionPlans()
    {
        $team_plan_array = array(
            'Annual 4 User License',
            'Annual 20 User License',
            'Annual Company Wide Unlimited User License',
            'Annual 15 User Education License',
            'Annual UNLIMITED User Institute Wide License',
            'Daily'
        );
        
        return $team_plan_array;
    }
    
    public function getPlanLimit($subscription_plan,$childSubscriptionCount,$parent_id)
    {
        $no_of_users = $this->SubscriptionHelper->getUsers($parent_id);
        switch($subscription_plan){
            case "Daily":
                $userlimit = 4;
                $userlimit = ($no_of_users) ? $no_of_users : $userlimit;
                $remaining_limit = $userlimit - $childSubscriptionCount;
                break;
            case "Annual 4 User License":
                $userlimit = 3;
                $userlimit = ($no_of_users) ? $no_of_users : $userlimit;
                $remaining_limit = $userlimit - $childSubscriptionCount;
                break;
            case "Annual 20 User License":
                $userlimit = 19;
                $userlimit = ($no_of_users) ? $no_of_users : $userlimit;
                $remaining_limit = $userlimit - $childSubscriptionCount;
                break;
            case "Annual Company Wide Unlimited User License":
                $userlimit = 200;      
                $userlimit = ($no_of_users) ? $no_of_users : $userlimit;
                $remaining_limit = $userlimit - $childSubscriptionCount;
                break;
            case "Annual 15 User Education License":
                $userlimit = 14;
                $userlimit = ($no_of_users) ? $no_of_users : $userlimit;
                $remaining_limit = $userlimit - $childSubscriptionCount;
                break;
            case "Annual UNLIMITED User Institute Wide License":
                $userlimit = 200;
                $userlimit = ($no_of_users) ? $no_of_users : $userlimit;
                $remaining_limit = $userlimit - $childSubscriptionCount;
                break;
            default:
                $userlimit = 0;
                $remaining_limit = 0;
                break;
        }

        return array($userlimit,$remaining_limit,$no_of_users);
    }

    public function UnSubscribeChild($subscription_history_detail,$status)
    {
            
        $date = $this->date->gmtDate();
        $sub_period_lable = $subscription_history_detail->getSubscriptionPeriod();
        $team_plan_array = $this->getTeamSubscriptionPlans();

        if(in_array($sub_period_lable,$team_plan_array))
        {
            $parentid = $subscription_history_detail->getCustomerId();
            $child_customers = $this->SubscriptionHelper->getChildSubscriptionHistory($parentid);

            foreach ($child_customers as $child) {
                
                if(empty($child->getChildCustomerId())) :
                    continue;
                endif;
                
                $customer_id = $child->getChildCustomerId();
                
                $subscription_history_detail = $this->_subscription->create()->getLastPaidSubscriptionhistory($customer_id); 

                if($subscription_history_detail == "Unsubscribed" && $status == "Unsubscribed") :
                    continue;
                endif;
                
                $subscription_history_detail->setData("status_success", $status);
                $subscription_history_detail->setData("update_time", $date);
                $subscription_history_detail->save();
            }
        }
    }

    public function addChildSubscription($sub_period_lable,$customer_id,$sub_from_date,$subscription_order_start_date,$sub_end_date,$payment_status,$increment_id,$download_limit)
    {
        $team_plan_array = $this->getTeamSubscriptionPlans();
        if(in_array($sub_period_lable,$team_plan_array))
        {
            $child_customers = $this->SubscriptionHelper->getChildSubscriptionHistory($customer_id);
            if ($child_customers->getSize() > 0 ) {
                
                foreach ($child_customers as $child) {

                    if(empty($child->getChildCustomerId())) :
                        continue;
                    endif;

                    $newSubscription = $this->_subscription->create();
                    $newSubscription->setFromDate($sub_from_date)->setSubscriptionStartDate($subscription_order_start_date)
                    ->setToDate($sub_end_date)->setRenewDate($sub_end_date)
                    ->setStatusSuccess($payment_status)->setIncrementId($increment_id)
                    ->setSubscriptionPeriod($sub_period_lable)->setParentCustomerId($child->getParentCustomer())->setCustomerId($child->getChildCustomerId())->setDownloadLimit($download_limit)->setAdminModified(1)->save();
                    
                }
            }
        }
    }
}