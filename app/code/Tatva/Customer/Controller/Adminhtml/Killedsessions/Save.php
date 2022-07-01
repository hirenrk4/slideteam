<?php

namespace Tatva\Customer\Controller\Adminhtml\Killedsessions;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;


class Save extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Customer\Model\Killedsesssions $killedSessions, 
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->killedSessions = $killedSessions;
        $this->messageManager = $messageManager;
    }

    public function execute() {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $post = (array) $this->getRequest()->getPost();
        //echo "<pre>";print_r($post);die("stop");
        if (!empty($post)) {
            try {
                if(isset($post['id']))
                {
                    $this->killedSessions->load($post['id']);
                    $this->killedSessions->addData($post);
                    $this->killedSessions->save();
                    
                    $this->messageManager->addSuccess(__('Customer data was successfully saved'));
                    $this->_redirect('tatvacustomer/killedsessions/index');
                    return;    
                } 
            } catch (Exception $e) {
                $this->messageManager->addError(
                        __('Customer data not saved')
                );
            } 
                
        }   
    }

}
