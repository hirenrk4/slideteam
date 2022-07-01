<?php

namespace Tatva\Customer\Observer;

class CheckUserCreateObserver extends \Magento\Captcha\Observer\CheckUserCreateObserver
{
	protected $_scopeConfig;

	public function __construct
	(
		\Magento\Captcha\Helper\Data $helper,
		\Magento\Framework\App\ActionFlag $actionFlag,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\Session\SessionManagerInterface $session,
		\Magento\Framework\UrlInterface $urlManager,
		\Magento\Framework\App\Response\RedirectInterface $redirect,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Captcha\Observer\CaptchaStringResolver $captchaStringResolver
	) 
	{
		$this->_helper = $helper;
		$this->_actionFlag = $actionFlag;
		$this->messageManager = $messageManager;
		$this->_session = $session;
		$this->_urlManager = $urlManager;
		$this->redirect = $redirect;
		$this->captchaStringResolver = $captchaStringResolver;
		$this->_scopeConfig = $scopeConfig;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$master_enable = $this->_scopeConfig->getValue('customer/captcha/master_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$master_captcha = $this->_scopeConfig->getValue('customer/captcha/master_captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		$formId = 'user_create';
		$captchaModel = $this->_helper->getCaptcha($formId);
		if ($captchaModel->isRequired()) 
		{
			/** @var \Magento\Framework\App\Action\Action $controller */
			$controller = $observer->getControllerAction();
			$captcha = $this->captchaStringResolver->resolve($controller->getRequest(), $formId);
	
			if($master_enable)
			{
				if(!$captchaModel->isCorrect($captcha) && $captcha != $master_captcha) 
				{
					$this->notValidCpatcha($controller);
				}
			}
			else
			{
				if(!$captchaModel->isCorrect($captcha)) 
				{
					$this->notValidCpatcha($controller);	
				}
			}
		}
		return $this;
	}
	
	protected function notValidCpatcha($controller)
	{
		$this->messageManager->addError(__('Incorrect CAPTCHA'));
		$this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
		$this->_session->setCustomerFormData($controller->getRequest()->getPostValue());
		$url = $this->_urlManager->getUrl('*/*/create', ['_nosecret' => true]);
		$controller->getResponse()->setRedirect($this->redirect->error($url));
	}
}
