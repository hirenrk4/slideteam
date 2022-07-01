<?php

namespace Tatva\Customer\Plugin\Controller\Account;

use Magento\Customer\Model\Session;

class LoginPostAfter {

    /**
     * [$_customerSession ]
     * @var [\Magento\Customer\Model\Session]
     */
    protected $_customerSession;

    protected $_customerRedirectionFlow;


    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Tatva\Customer\Model\CustomerRedirectionFlow $customerRedirectionFlow
        ) {
        $this->_customerSession = $customerSession;
        $this->_customerRedirectionFlow = $customerRedirectionFlow;
    }

    public function afterExecute(\Magento\Customer\Controller\Account\LoginPost $loginpost,$result)
	{
        $redirectUrlForCustomer = $this->_customerRedirectionFlow->getRedirectUrlForCustomer();
        
        $this->_customerSession->setBeforeAuthUrl($redirectUrlForCustomer);
        $this->_customerSession->setAfterAuthUrl($redirectUrlForCustomer);

        if($this->_customerSession->isLoggedIn()){
            $result->setUrl($redirectUrlForCustomer);
        }


        return $result;
	}

}
