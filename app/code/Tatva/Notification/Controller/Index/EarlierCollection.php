<?php
namespace Tatva\Notification\Controller\Index;
use Tatva\Notification\Model\NotificationFactory;
use Magento\Framework\Controller\Result\JsonFactory;
class EarlierCollection extends \Magento\Framework\App\Action\Action
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
        $pageNumber = $this->getRequest()->getParam('page_number');        
        ++$pageNumber;
        
        $data = array('pageNumber'=>$pageNumber);        
        $block = $resultPage->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Tatva_Notification::EarlierCollection.phtml')
                ->setData('data',$data)
                ->toHtml(); 		
        $result->setData(
        	[
        		'output' => $block,
        		'pageNumber' => $pageNumber
        	]
        );
        return $result;
	}
}