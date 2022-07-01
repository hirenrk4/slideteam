<?php
namespace Tatva\Customer\Block\Form;

use Magento\Customer\Model\AccountManagement;

class Login extends \Magento\Customer\Block\Form\Login
{
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, 
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Customer\Model\Url $customerUrl,
            \Magento\Framework\Session\SessionManagerInterface $sessionManagerInterface,
            array $data = array()) {
        parent::__construct($context, $customerSession, $customerUrl, $data);
        $this->sessionManagerInterface=$sessionManagerInterface;
    }
    
    /**
     * Get minimum password length
     *
     * @return string
     * @since 100.1.0
     */
    public function getMinimumPasswordLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    public function getSession()
    {
        return $this->sessionManagerInterface;
    }

    
}