<?php
namespace Tatva\Downloadable\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsavebefore implements ObserverInterface
{

	public function __construct(
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
		\Magento\Framework\Session\SessionManagerInterface $coreSession
	) 
	{
		$this->_request = $request;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->_coreSession = $coreSession;
	}
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$runafterimport = $this->_coreSession->getRunAfterImport();
		if(isset($runafterimport) && !empty($runafterimport))
		{
			return;
		}
		$postData = $this->_request->getPostValue();
		$resultRedirect = $this->resultRedirectFactory->create();
		if($postData){
			try {
				if($postData['is_downloadable'] == 1 && !isset($postData['downloadable']))
                {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save product. Add downloadable link'));  
                }
			} catch (Exception $e) {
				$this->messageManager->addExceptionMessage($e);
			}
		}
		return $resultRedirect;
	}
}