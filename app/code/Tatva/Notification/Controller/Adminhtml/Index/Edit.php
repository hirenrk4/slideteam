<?php

namespace Tatva\Notification\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Tatva\Notification\Model\ResourceModel\Notification as ResourceModelNotification;
use Tatva\Notification\Model\NotificationFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    
    //const ADMIN_RESOURCE = 'Tatva_Notification::edit';

    /**
     *  ResultPageFactory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     *  Session
     *
     * @var Session
     */
    protected $backendSession;
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

    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Session $backendSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        ResourceModelNotification $notificationResource,
        NotificationFactory $notificationFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context);
        $this->backendSession = $backendSession;
        $this->notificationResource = $notificationResource;
        $this->notificationFactory = $notificationFactory;
    }
    
    /**
     * Edit Newsletter Discount Form
     *
     * @return                                  \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');     
        $formModel = $this->notificationFactory->create();        
        //die("edit-notification");
        if ($id) {
            $this->notificationResource->load($formModel, $id);
            if (!$formModel->getId()) {
                $this->messageManager->addError(__('This notification no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $formModel->setData($data);
        }
        $resultPage = $this->resultPageFactory->create();
        $this->coreRegistry->register('notification_form', $formModel);
        $resultPage->addBreadcrumb(
            $id ? __('Edit Notification') : __('New Notification'),
            $id ? __('Edit Notification') : __('New Notification')
        );
        $resultPage->getConfig()->getTitle()
            ->prepend($id ? __('Edit Notification') : __('Add Notification'));        
        return $resultPage;
    }
}
