<?php

namespace Tatva\Subscription\Block;


class Subscription extends \Magento\Framework\View\Element\Template {

    protected $pager;
    protected $_orderCollectionpro;
    protected $_customerRepositoryInterface;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, 
        \Tatva\Subscription\Model\Subscription $subscription, 
        \Tatva\Subscription\Model\ResourceModel\Subscription\CollectionFactory $subscriptionCollectionFactory,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\App\ResponseFactory $responseFactory, 
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionpro,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Tatva\TcoCheckout\Model\IpnDataFactory $ipnDataFactory,
        \Tatva\Unsubscribeuser\Model\UnsubscribeFactory $UnsubscribeFactory, 
        array $data = []
        ) {
        // $this->registry = $registry;
        $this->subscription = $subscription;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
         $this->_responseFactory = $responseFactory;
         $this->_orderCollectionpro = $orderCollectionpro;
         $this->_dateFactory = $dateTimeDateTimeFactory;
         $this->_customerSession = $customerSession;
         $this->_customerRepositoryInterface = $customerRepositoryInterface;
         $this->_ipnDataFactory = $ipnDataFactory;
         $this->_unsubscribeFactory = $UnsubscribeFactory;
        parent::__construct(
            $context, $data
            );
    }

  public function _prepareLayout() {
    parent::_prepareLayout();
    $this->pageConfig->getTitle()->set(__('My Subscriptions'));
    if ($this->subscription->getCustomerSubscriptions()) {
        $pager = $this->getLayout()->createBlock(
            '\Tatva\Downloadscount\Block\Pager',
            'downloadscount.items.pager');

        $pager->setAvailableLimit(array(5=>5,10=>10,15=>15,20=>20)); 
        $pager->setShowPerPage(true);  
        $pager->setCollection($this->subscription->getCustomerSubscriptions());  
        $this->setChild('pager', $pager);
        $this->subscription->getCustomerSubscriptions()->load();
    }
    return $this;
}

public function getLoginCustomerId()
{
    return $this->_customerSession->getCustomer()->getId();
}

public function redirectOnLoginPage()
{
	 $this->_responseFactory->create()->setRedirect($this->getUrl("customer/account/login"))->sendResponse();
}

public function getPagerHtml() {
    return $this->getChildHtml('pager');
}

public function getSubscription() {
    if (!$this->hasData('subscription')) {
        $this->setData('subscription', $this->registry->registry('subscription'));
    }
    return $this->getData('subscription');
}

public function checkCustomerIpnWait()
{
    $customerEmail = $this->_customerSession->getCustomer()->getEmail();
    $ipnData = $this->_ipnDataFactory->create()->getCollection();
    $ipnData->addFieldToFilter("customer_email",array("eq"=>$customerEmail));

    $count = $ipnData->getSize();
    if($count > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}

public function checkIsUnsubscribeRequest()
{
    $customerId = $this->getLoginCustomerId();
    $model = $this->_unsubscribeFactory->create();
    $collection = $model->getCollection()->addFieldToFilter('customer_id', array('eq' => $customerId))->addFieldToFilter('status',array('eq'=>'pending')); 

    return $collection->getSize(); 
}

public function getCustomerSubscriptions() {
    $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
    $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest
    ()->getParam('limit') : 5;
    $collection = $this->subscription->getCustomerSubscriptions();

    if($collection != false)
    {
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->setOrder("subscription_history_id","DESC");

        return $collection;
    }

    return false;
}

public function getLastCustomerOrder()
{
    $customerId = $this->subscription->getOrderCustomerId();
    
    $collection = $this->_orderCollectionpro->create();
    $collection->addFieldToFilter('customer_id',['eq'=>$customerId])->setOrder('entity_id','DESC');
    $collection->getSelect()->limit(1);
    
    return $collection->getData();
}

public function getChildCustomerOrder()
{
    $customerId = $this->subscription->getOrderCustomerId();
    
    $collection = $this->subscriptionCollectionFactory->create()->addFieldToFilter('parent_customer_id',['eq'=>$customerId]);
    return $collection;
}

public function getCurrentGmtDate()
{
    return $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
}

public function getLastPaidSubscriptionhistory() {
    $collection = $this->subscription->getLastPaidSubscriptionhistory();
    return $collection;
}
 public function CustomformatDate($date)
    {

        if (empty($date)) {
            return null;
        }
        // unix timestamp given - simply instantiate date object
        if (is_scalar($date) && preg_match('/^[0-9]+$/', $date)) {
            $date = (new \DateTime())->setTimestamp($date);
        } elseif (!($date instanceof \DateTimeInterface)) {
            // normalized format expecting Y-m-d[ H:i:s]  - time is optional
            $date = new \DateTime($date);
        }
        return $date->format('m/d/Y');
    }
    public function getCustomerEmail($customerId)
    {
        $customer = $this->_customerRepositoryInterface->getById($customerId);
        $email = $customer->getEmail();        
        return $email;
    }
}

    