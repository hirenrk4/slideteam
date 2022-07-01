<?php

namespace Tatva\Notification\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Tatva\Notification\Model\ResourceModel\Notification as ResourceModelNotification;
use Tatva\Notification\Model\NotificationFactory;

class Delete extends \Magento\Backend\App\Action
{

    /**
     *  Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    const ADMIN_RESOURCE = 'Tatva_Notification::delete';
    
    /**
     *  ResourceModelForm
     *
     * @var ResourceModelForm
     */
    protected $notificationResource;
    /**
     *  FormFactory
     *
     * @var FormFactory
     */
    protected $notificationFactory;

    /**
     * Delete constructor.
     *
     * @param Context           $context      context
     * @param Filesystem        $filesystem   filesystem
     * @param File              $file         file
     * @param ResourceModelForm $formResource formResource
     * @param FormFactory       $formFactory  formFactory
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        File $file,
        ResourceModelNotification $notificationResource,
        NotificationFactory $notificationFactory
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->_file = $file;
        $this->notificationResource = $notificationResource;
        $this->notificationFactory = $notificationFactory;
    }
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
               
                $formModel = $this->notificationFactory->create();
                $this->notificationResource->load($formModel, $id);
                $this->notificationResource->delete($formModel);
                $this->messageManager->addSuccess(__('The Notification has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a Notification to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
