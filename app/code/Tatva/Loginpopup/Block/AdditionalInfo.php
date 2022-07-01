<?php

namespace Tatva\Loginpopup\Block;

class AdditionalInfo extends \Magento\Framework\View\Element\Template
{
    protected $_customer;
    protected $_customerSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        array $data = [])
    {
        $this->_customerSession = $customerSession;
        $this->_customer = $customer->load($this->_customerSession->getCustomer()->getId());
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    public function getIndustry()
    {
        return $this->_customer->getIndustryAttribute();
    }

    public function getjobProfile()
    {
        return $this->_customer->getJobProfileAttribute();
    }
}
