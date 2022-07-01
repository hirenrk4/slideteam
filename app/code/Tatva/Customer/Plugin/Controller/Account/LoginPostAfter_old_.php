<?php

namespace Tatva\Customer\Plugin\Controller\Account;

use Magento\Customer\Model\Session;

class LoginPostAfter {

    protected $_redirect;
    protected $_request;
    protected $_responseFactory;
        
    public function __construct(
                            \Magento\Framework\App\Response\RedirectInterface $redirect,
                            \Magento\Framework\App\ResponseFactory $responseFactory,
                            \Magento\Framework\App\Request\Http $request
                         ) {

        $this->_redirect = $redirect;
        $this->_request = $request;
        $this->_responseFactory = $responseFactory;
    }

    public function afterExecute(\Magento\Customer\Controller\Account\LoginPost $loginpost)
	{
        $redirectUrl = $this->_redirect->getRedirectUrl();
        $referer = $this->_request->getParam('referer');
        if($referer){
            $redirectUrl  = base64_decode($referer);
        }
        if($redirectUrl){
            $this->_responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
            die();
        }

	}

}
