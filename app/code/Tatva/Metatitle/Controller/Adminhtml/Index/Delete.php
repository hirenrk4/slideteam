<?php
namespace Tatva\Metatitle\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Metatitle\Model\Metatitle $metatitle
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->metatitle = $metatitle;
        }
    public function execute(){
        $id = $this->getRequest()->getParam('object_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if( $id > 0 ) {
            try {
                $this->metatitle->load($id);
                $this->metatitle->delete();
                $this->messageManager->addSuccess(__('You have deleted the object.'));
                // go to grid
                return $resultRedirect->setPath('*/*/index');
            } 
            catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                    // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can not find an object to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

}