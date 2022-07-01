<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Import;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Testimonial Import'));
        return $resultPage;
    }
    
}