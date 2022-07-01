<?php
namespace Tatva\Loginpopup\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute(){
        $id = $this->getRequest()->getParam('object_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if( $id > 0 ) {
            try {
                $model = $this->_objectManager->create('Tatva\Loginpopup\Model\CustomerAdditionlData');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Item was successfully deleted.'));
                // go to grid
                return $resultRedirect->setPath('*/*/index');
            } 
            catch (Exception $e) {
             $this->messageManager->addError($e->getMessage());
                    // go back to edit form
             return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
         }
     }
     $this->messageManager->addError(__('We can not find an Item to delete.'));
        // go to grid
     return $resultRedirect->setPath('*/*/');
 }

}