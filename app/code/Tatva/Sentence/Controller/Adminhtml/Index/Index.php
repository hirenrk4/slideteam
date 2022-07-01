<?php

namespace Tatva\Sentence\Controller\Adminhtml\Index;
//var_dump("hii");die;
class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory = false;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
       // var_dump($this->resultPageFactory);die;
    }

    public function execute()
    {
    	//var_dump($_REQUEST);die;

    	if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        
        $resultPage->getConfig()->getTitle()->prepend((__('Sentences')));
        return $resultPage;
    }

}
