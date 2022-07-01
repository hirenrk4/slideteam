<?php
namespace Tatva\Deleteaccount\Block;

class Deleteaccount extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;

    public function __construct
    (
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Tatva\Subscription\Block\Subscription $subscription,
        array $data = []
    )
    {
        parent::__construct($context,$data);
        $this->customerSession = $customerSession;
        $this->subscriptionBlock = $subscription;
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }
    
    public function getLastCustomerOrder()
    {
        return $this->subscriptionBlock->getLastCustomerOrder();
    }
    
    public function getCurrentGmtDate()
    {
        return $this->subscriptionBlock->getCurrentGmtDate();
    }

}