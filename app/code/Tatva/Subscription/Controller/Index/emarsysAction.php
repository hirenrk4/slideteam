<?php

namespace Tatva\Subscription\Controller\Index;

class emarsysAction extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		// Need to implement differently
		/*$product_id = !empty($_GET['product_id']) ? $_GET['product_id'] : "";
		$customer_id = !empty($_GET['customer_id']) ? $_GET['customer_id'] : "";
		$share = !empty($_GET['share']) ? $_GET['share'] : "";

		$cart = $this->checkoutCartFactory->create()->getQuote();
		$productToEmarsysCart = 1;
		if($cart->getItemsQty() > 0)
		{
			$productToEmarsysCart = 0;
		}
		$data = $this->subscriptionHelper->candownloadableproduct($product_id, $customer_id, $share);
		$arr = array('success_return'=>$data,'productToEmarsysCart'=>$productToEmarsysCart);
		echo json_encode($arr);
		return $this->_pageFactory->create();*/
	}
}

