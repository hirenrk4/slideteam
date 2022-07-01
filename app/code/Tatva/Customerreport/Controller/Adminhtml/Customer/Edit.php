<?php
namespace Tatva\Customerreport\Controller\Adminhtml\Customer;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Framework\App\Action\Action
{
	 protected $resultPageFactory = false;
		 public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
    }
		public function execute()
		{			
			$id=$this->request->getParam('entity_id');
			$resultPage =  $this->resultPageFactory->create(); 
			 $resultPage->getConfig()->getTitle()->prepend((__('Add Subscription')));

            return $resultPage;   
		} 

}
