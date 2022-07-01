<?php

namespace Tatva\Customproductreview\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;


class Save extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Customproductreview\Model\Customproductreview $productreview, 
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productreview = $productreview;
        $this->messageManager = $messageManager;
        $this->timezone = $timezone;
    }

    public function execute() {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $post = (array) $this->getRequest()->getPost('Customproductreview');
        if (!empty($post)) {
            try {
                if(!isset($post['review_id']))
                {
                    $this->productreview->addData($post);
                    $this->productreview->save();

                    $this->messageManager->addSuccess(__('Product Review was successfully saved'));
                    $this->_redirect('customproductreview/index/index');
                    return;    
                } else{

                    $id = $post['review_id'];

                    $this->productreview->load($id);
                    $this->productreview->addData($post);
                    $this->productreview->save();
                    $this->messageManager->addSuccess(__('Product Review was successfully saved'));
                    
                    //check for `back` parameter
                    if ($this->getRequest()->getParam('back')) {
                        return $resultRedirect->setPath('*/*/edit', ['review_id' => $this->productreview->getReviewId(), '_current' => true]);
                    }

                    return $resultRedirect->setPath('*/*/');
                }    
                
            } catch (Exception $e) {
                $this->messageManager->addError(
                        __('Cannot save product review')
                );
            } 
                
        }   
    }

}
