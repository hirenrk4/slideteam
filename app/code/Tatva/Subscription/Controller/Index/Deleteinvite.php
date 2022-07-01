<?php

namespace Tatva\Subscription\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Tatva\Subscription\Model\Subscription;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Tatva\Subscription\Model\SubscriptionInvitationFactory;

class Deleteinvite extends Action
{
    protected $resultPageFactory;
    protected $subscriptionFactory;
 
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SubscriptionInvitationFactory $subscriptioninvitation,
        Subscription $subscriptionFactory,
        \Tatva\Subscription\Helper\Data $subscriptionhelper
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_subscriptioninvitation = $subscriptioninvitation;
        $this->subscription = $subscriptionFactory;
        $this->subscriptionhelper = $subscriptionhelper;

        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $data = (array)$this->getRequest()->getParams();
            if ($data['id'] || $data['invitation_id']) {
                if ($data['id']) {

                    $ChildSubscription = $this->subscriptionhelper->getChildCustomerSubscription($data['id']);
                    
                    $model = $this->subscription->load($ChildSubscription->getSubscriptionHistoryId());
                    $model->setStatusSuccess("Unsubscribed")->setDownloadLimit(0)->save();
                }
                if ($data['invitation_id']) {
                    $invitationmodel = $this->_subscriptioninvitation->create();
                    $invitationmodel->load($data['invitation_id']);
                    $invitationmodel->delete();
                }
                $this->messageManager->addSuccessMessage(__("Subscription Delete Successfully."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e, __("We can\'t delete subscription, Please try again."));
        }
       return $this->_redirect('subscription/index/childlist');
    }
}