<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class Index extends \Magento\Backend\App\Action
{
    /**
     * @return void
     */
     public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
       parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }
    
   public function execute()
   {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Vgroup65_Testimonial::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Testimonial'));
 
        return $resultPage;
   }
}