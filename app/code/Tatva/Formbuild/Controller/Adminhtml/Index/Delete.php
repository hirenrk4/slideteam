<?php
namespace Tatva\Formbuild\Controller\Adminhtml\Index;

use Tatva\Formbuild\Model\Post as Post;


class Delete extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Tatva_Formbuild::delete';

    protected $resultPageFactory;
    protected $postFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Formbuild\Model\PostFactory $postFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->postFactory = $postFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $post = $this->postFactory->create()->load($id);

        if(!$post)
        {
            $this->messageManager->addError(__('Unable to process. please, try again.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/', array('_current' => true));
        }

        try{
            $post->delete();
            $this->messageManager->addSuccess(__('Your form has been deleted !'));
        }
        catch(\Exception $e)
        {
            $this->messageManager->addError(__('Error while trying to delete form'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', array('_current' => true));
    }
}