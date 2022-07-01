<?php
namespace Tatva\Unsubscribepaypal\Controller\Adminhtml\Index;


class Unsubscribe extends \Magento\Framework\App\Action\Action
{


  public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Backend\Model\Auth\Session $backendAuthSession,
    \Tatva\Subscription\Model\Subscription $subscriptionSubscription,
    \Magento\Framework\Session\Generic $generic,
    \Magento\Framework\UrlInterface $urlInterface,
    \Magento\Backend\Helper\Data $_helper  
    ) {
    $this->backendAuthSession = $backendAuthSession;
    $this->subscriptionSubscription = $subscriptionSubscription;
    $this->generic = $generic;
    $this->_urlInterface = $urlInterface;
    $this->_helper = $_helper;
    parent::__construct($context);
  }

  public function execute()
  {
   $customer_id = $this->getRequest()->getParam("customer_id");
   $this->backendAuthSession->setadmincustomer($customer_id);
   $subscription_history_detail = "";
   $subscription_history_detail = $this->subscriptionSubscription;
   $last_sub_detail = $subscription_history_detail->getLastPaidSubscriptionhistory($customer_id);

   $subscription_history_id = $last_sub_detail->getSubscriptionHistoryId();
  $url='paypalrec_admin/unsubscribe/index';
    $params= [
              "pflag" => "adminunsub",
              "subscription_id" => $subscription_history_id,
              "customer_id" => $customer_id
              ];

            $path=$this->_helper->getUrl($url,$params);

   $this->_redirect($path);
 }



}