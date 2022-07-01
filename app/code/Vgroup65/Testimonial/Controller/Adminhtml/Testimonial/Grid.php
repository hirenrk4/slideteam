<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class Grid extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
       parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }
   public function execute()
   {
      return $this->_resultPageFactory->create();
   }
}
 