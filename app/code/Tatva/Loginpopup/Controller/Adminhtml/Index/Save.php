<?php
namespace Tatva\Loginpopup\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute() 
    {
        $post = (array) $this->getRequest()->getPost('loginpopup');
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!empty($post)) {
            try {
                $model = $this->_objectManager->create("Tatva\Loginpopup\Model\CustomerAdditionlData");

                $id = $this->getRequest()->getParam('id');
                if($id >0){
                    $params = (array) $this->getRequest()->getPost('loginpopup');
                    $model->load($id);
                    $model->addData($params);
                    $model->save();
                    $this->messageManager->addSuccessMessage("Customer survey data was successfully updated");
                    //$this->_redirect('booking/index/booking');
                }
                else{
                    foreach($post as $key => $value){
                        $model->setData($key, $value);
                    }
                    $model->save();
                $this->messageManager->addSuccessMessage("Customer survey data was successfully saved");
                //$this->_redirect('booking/index/booking');
                }
                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit",['id' => $model->getId(), '_current' => true]);
                    return;
                }
                return $resultRedirect->setPath('*/*/');
            } 
            catch (Exception $e) {
                $this->messageManager->addError(
                    __('We can\’t process your request right now. Sorry, that\’s all we know.')
                );
            }  
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                   
        }

        return $resultRedirect->setPath('*/*/');
    }
    
  }