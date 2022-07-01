<?php

namespace Tatva\Notification\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Backend\Model\Session;
use Tatva\Notification\Model\ResourceModel\Notification as ResourceModelNotification;
use Tatva\Notification\Model\NotificationFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem\Io\File;
use \Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
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
    /**
     *  DateFactory
     *
     * @var DateFactory
     */
    protected $dateFactory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var File
     */
    protected $file;
    protected $authSession;
    /**
     * Construct
     *
     * @param Action\Context    $context        context
     * @param Session           $backendSession backendSession
     * @param ResourceModelForm $formResource   formResource
     * @param FormFactory       $formFactory    formFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Session $backendSession,
        ResourceModelNotification $notificationResource,
        NotificationFactory $notificationFactory,
        DateTimeFactory $dateTimeDateTimeFactory,
        UploaderFactory $uploaderFactory,
        File $file,
        DirectoryList $directoryList,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        parent::__construct($context);
        $this->backendSession = $backendSession;
        $this->notificationResource = $notificationResource;
        $this->notificationFactory = $notificationFactory;
        $this->dateFactory = $dateTimeDateTimeFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->file = $file;
        $this->directoryList = $directoryList;
        $this->authSession = $authSession;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        $date = $this->converToTz($data['publishe_at'],'GMT','America/Los_Angeles');
        $data['publishe_at']=$date;
        if($data['customer_type'] != 2){
            $data['paid_duration']=4;
        }            
        $data['created_by']=$this->authSession->getUser()->getUsername();        
        $resultRedirect = $this->resultRedirectFactory->create();
        $formModel = $this->notificationFactory->create();

        if ($data) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->notificationResource->load($formModel, $id);
            }

            $this->_eventManager->dispatch(
                'notification_prepare_save',
                ['notification' => $formModel, 'request' => $this->getRequest()]
            );
            
            try {
                $this->notificationResource->save($formModel->setData($data));
                $this->messageManager->addSuccess(__('You saved the notification.'));
                $this->backendSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $formModel->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('notification/index/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the rule.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['notification_id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('notification/index/index');
    }
    protected function converToTz($dateTime="", $toTz='', $fromTz='')
    {
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('Y-m-d H:i:s');
        return $dateTime;
    }
}
