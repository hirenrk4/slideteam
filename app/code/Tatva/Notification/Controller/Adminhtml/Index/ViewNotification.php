<?php
namespace Tatva\Notification\Controller\Adminhtml\Index;
use Tatva\Notification\Model\NotificationFactory;
use Magento\Framework\Controller\Result\JsonFactory;
class ViewNotification extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    /**     
     * @var \Tatva\Notification\Model\NotificationFactory
     */
    protected $notificationFactory;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        NotificationFactory $notificationFactory,
        JsonFactory $resultJsonFactory
    )
    {
        $this->pageFactory = $pageFactory;
        $this->notificationFactory = $notificationFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    public function execute()
    {       
        $result = $this->resultJsonFactory->create();
        $resultPage = $this->pageFactory->create();
        $id = $this->getRequest()->getParam('id');                

        $collection = $this->notificationFactory->create()->getCollection();
        $collection->addFieldToFilter('notification_id', ['in' => $id]);
 
        $block = $resultPage->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Tatva_Notification::ViewNotification.phtml')
                ->setData('data',$collection)
                ->toHtml();         
        $result->setData(
            [
                'output' => $block                
            ]
        );
        return $result;
    }
}