<?php
namespace Tatva\Tag\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Framework\App\Action\Action
{
	 protected $resultPageFactory = false;
		 public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->_storeManager = $storeManager;
    }
		public function execute()
		{		

           if (!(int) $this->getRequest()->getParam('store')) {
            return $this->_redirect('*/*/*/', array('store' => $this->_storeManager->getStore()->getId(), '_current' => true));
        }
        $resultPage =  $this->resultPageFactory->create(); 
        
            $title='New Tag';
			$id=$this->request->getParam('tag_id');
			if($id>0)
            {
                $title='Edit Tag '.$id;
            }
			 $resultPage->getConfig()->getTitle()->prepend((__($title)));

            return $resultPage;   
		} 

}
