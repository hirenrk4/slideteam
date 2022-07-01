<?php
namespace Tatva\Unsubscribetco\Controller\Adminhtml\Index;


class Unsubscribefinal extends \Magento\Framework\App\Action\Action
{


  public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
    $this->messageManager = $messageManager;
    parent::__construct($context);
  }

  public function execute()
  {
       $message =$this->messageManager->addSuccessMessage('Customer has been unsubscribed successfully.');
        $this->_redirect('*/*/index');
 }



}