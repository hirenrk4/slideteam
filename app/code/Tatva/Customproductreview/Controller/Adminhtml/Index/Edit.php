<?php
namespace Tatva\Customproductreview\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory = false;
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
		) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}
	public function execute()
	{			
		return $this->resultPageFactory->create();    
	} 

}
