<?php
namespace Tatva\Notification\Controller\Index;
use Tatva\Notification\Model\DeleteNotificationFactory;
use Magento\Framework\Controller\Result\JsonFactory;
class DeleteNotification extends \Magento\Framework\App\Action\Action
{
	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */
	protected $pageFactory;
	/**     
     * @var \Tatva\Notification\Model\NotificationFactory
     */
    protected $deleteNotificationFactory;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		DeleteNotificationFactory $deleteNotificationFactory,
		JsonFactory $resultJsonFactory
	)
	{
		$this->pageFactory = $pageFactory;
		$this->deleteNotificationFactory = $deleteNotificationFactory;
		$this->resultJsonFactory = $resultJsonFactory;
		return parent::__construct($context);
	}

	public function execute()
	{		
		$result = $this->resultJsonFactory->create();
        $resultPage = $this->pageFactory->create();
        $notificationId = $this->getRequest()->getParam('notificationId');
        $customerId = $this->getRequest()->getParam('customerId');        
        
        $model = $this->deleteNotificationFactory->create();
        $model->addData([
            "notification_id" => $notificationId,
            "customer_id" => $customerId
            ]);
        $saveData = $model->save();
        if($saveData){
            // $this->messageManager->addSuccess( __('Delete Notification Successfully !') );
            $response='Delete Notification Successfully !';
        }else{
            $response='Delete Notification Not Successfully !';
        }
       
        $result->setData(
        	[
        		'output' => $response
        	]
        );
        return $result;
	}
}