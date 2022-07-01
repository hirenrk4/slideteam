<?php

namespace Tatva\Popup\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;


class Save extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Popup\Model\Popup $popup, 
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->popup = $popup;
        $this->messageManager = $messageManager;
        $this->timezone = $timezone;
    }

    public function execute() {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $post = (array) $this->getRequest()->getPost('Popup');
        if (!empty($post)) {
            $modelCollection = $this->popup->getCollection()->addFieldToFilter('identifier',$post['identifier']);
            try {
                if(!isset($post['popup_id']))
                {
                    if(empty($modelCollection->getData()))
                    {
                        $this->popup->addData($post);
                        $this->popup->save();

                        $this->messageManager->addSuccess(__('Popup was successfully saved'));
                        $this->_redirect('tatva_popup/index/index');
                        return;
                    } else {
                        $this->messageManager->addError(__('Identifier already exist'));
                        $this->_redirect('tatva_popup/index/index');
                        return; 
                    }
                    
                } else{

                    $id = $post['popup_id'];

                    $this->popup->load($id);
                    $this->popup->addData($post);
                    $this->popup->save();
                    $this->messageManager->addSuccess(__('Popup was successfully saved'));
                    $this->_redirect('tatva_popup/index/index');
                    return;
                }    
                
            } catch (Exception $e) {
                $this->messageManager->addError(
                        __('Cannot save, popup already exists.')
                );
            } 
                
        }   
    }

}
