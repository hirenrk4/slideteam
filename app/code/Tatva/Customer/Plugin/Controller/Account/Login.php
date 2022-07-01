<?php 

namespace Tatva\Customer\Plugin\Controller\Account;

use Magento\Customer\Model\Session;

class Login
{

	protected $_redirect;

	/**
	 * [$_customerSession ]
	 * @var [\Magento\Customer\Model\Session]
	 */
	protected $_customerSession;


	public function __construct(
		\Magento\Framework\App\Response\RedirectInterface $redirect,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		\Magento\Framework\App\RequestInterface $request
	)
	{
		$this->_redirect = $redirect;
		 $this->_coreSession = $coreSession;
	     $this->_request = $request;
	     $this->_customerSession = $customerSession;
	}

	public function beforeExecute(\Magento\Customer\Controller\Account\Login $login)
	{
		
		$socialPostData = $this->_coreSession->getSocialPostData();
		$productIdFromSession = 0;
		if($socialPostData){
			$productIdFromSession = isset($socialPostData['product_id']) ? $socialPostData['product_id'] : 0;
		}

		$socialData = array();
						
		$socialData['product_id'] = $this->_request->getParam('product_id') ? $this->_request->getParam('product_id') : $productIdFromSession;	
			
		$this->_coreSession->setSocialPostData($socialData);
	}
}