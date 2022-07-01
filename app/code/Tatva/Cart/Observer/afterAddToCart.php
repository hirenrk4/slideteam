<?php
namespace Tatva\Cart\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 *  Currently Not used
 */
class afterAddToCart implements ObserverInterface
{
	protected $_messageManager;
    protected $_url;
    protected $_checkoutSession;
    protected $_responseFactory;

    /**
	 *  Currently Not used
	 */
	public function __construct
	(
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\ResponseFactory $responseFactory,
		\Magento\Framework\UrlInterface $url
	) 
	{
		$this->_url = $url;
        $this->_checkoutSession = $checkoutSession;
		$this->_messageManager = $messageManager;
		$this->_responseFactory = $responseFactory;
	}

	/**
	 *  Currently Not used
	 */
	public function execute(\Magento\Framework\Event\Observer $observer)
	{      	                
  		$this->_checkoutSession->setNoCartRedirect(true);
    	$observer->getControllerAction()->getResponse()->setRedirect($this->_url->getUrl('checkout'));
    	$this->_responseFactory->create()->setRedirect($this->_url->getUrl('checkout'))->sendResponse();
        exit();
	}
}