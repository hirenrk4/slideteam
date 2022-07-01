<?php 
namespace Tatva\Free\Block\Customer;

class Login extends \Magento\Framework\View\Element\Template
{
    protected $_session;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        parent::__construct($context);
    }

    public function getCustomerSession()
    {
        return $this->_customerSession->isLoggedIn();
    }
    
}