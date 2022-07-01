<?php
namespace Tatva\Metatitle\Controller\Adminhtml\Index;


class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory = false;
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
    
        $resultPage = $this->resultPageFactory->create();
    
        $resultPage->getConfig()->getTitle()->prepend((__('Metatitle')));

        return $resultPage;
    }

}