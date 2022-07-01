<?php
namespace Tatva\Cart\Observer;
use Magento\Framework\Event\ObserverInterface;

class productAddToCartComplete implements ObserverInterface
{
	/**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    protected $_url;
    protected $_checkoutSession;
    protected $_responseFactory;
    protected $poduct;

	public function __construct
	(
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\ResponseFactory $responseFactory,
		\Magento\Framework\UrlInterface $url,
		\Magento\Catalog\Model\Product $product
	) 
	{
        $this->_url = $url;
        $this->_checkoutSession = $checkoutSession;
		$this->_messageManager = $messageManager;
		$this->_responseFactory = $responseFactory;
		$this->product = $product;
	}

	public function execute(\Magento\Framework\Event\Observer $observer){
		
		$product = $observer->getProduct();
		$productCheck = $product->load($product->getEntityId());	
		$isEbook = $productCheck->getIsEbook();
		if($isEbook == 1){
        	$message = __('You added %1 book to your shopping cart.',$product->getName());
        } else {
        	$message = __('You added %1 to your shopping cart.',$product->getName());
        }
        
        $this->_messageManager->addSuccessMessage($message);
    	$this->_checkoutSession->setNoCartRedirect(true);

		if($isEbook == 1){
    		// $observer->getResponse()->setRedirect($this->_url->getUrl('checkout/cart'));
    		$observer->getResponse()->setRedirect($this->_url->getUrl('checkout'));
		} else {
    		$observer->getResponse()->setRedirect($this->_url->getUrl('checkout'));
		}
		
    	return $this;
	}
}
