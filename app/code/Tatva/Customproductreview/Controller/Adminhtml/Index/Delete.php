<?php
namespace Tatva\Customproductreview\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Customproductreview\Model\Customproductreview $productreview
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productreview = $productreview;
        }
    public function execute(){
        $id = $this->getRequest()->getParam('object_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if( $id > 0 ) {
            try {
                $this->productreview->load($id);
                $this->productreview->delete();
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