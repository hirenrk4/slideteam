<?php
namespace Tatva\Deleteaccount\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Updatecoupon extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $resultJsonFactory;

    public function __construct
    (
    	Context $context,
        JsonFactory $resultJsonFactory, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();
        $result = $this->resultJsonFactory->create();

        $post = (array) $this->getRequest()->getPost();

        if (!empty($post)) 
        {

                $customer_id = $this->getRequest()->getParam('customer_id');
                $block = $resultPage->getLayout()
                ->createBlock('Tatva\Deleteaccount\Block\Updatecoupon')
                ->setTemplate('Tatva_Deleteaccount::Updatecoupon.phtml')
                ->setData('data',$customer_id)
                ->toHtml();
 
                $result->setData(['output' => $block]);
    
                return $result;
        }
            
            return $resultPage;
    }

}