<?php
namespace Tatva\Customproductreview\Controller\Adminhtml\Imports;

 class Index extends \Magento\Backend\App\Action
 {

    public function __construct(
        \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {

            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->prepend((__('Product Review')));

        return $resultPage;
    }


}