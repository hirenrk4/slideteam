<?php
namespace Tatva\Wishlist\Controller\Index;

class RemoveAll extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    
    protected $resultFactory;

    protected $wishlist;

    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Backend\App\Action\Context $context      
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->wishlist = $wishlist;
        parent::__construct($context);
    }

    public function execute()
    {
        $requestParams = $this->getRequest()->getParams();
        $customerId = isset($requestParams['customerId']) ? $requestParams['customerId'] : null;
        $wish = $this->wishlist->loadByCustomerId($customerId);
        $items = $wish->getItemCollection();
        $count = $items->getSize();
         foreach ($items as $item) 
         {
            $item->delete();
            $wish->save();
            if($count <= 1) 
            {    
                $this->messageManager->addSuccess(__('Product has been removed from your favourites.'));
            }
            else 
            {
                $this->messageManager->addSuccess(__('Products have been removed from your favourites.'));  
            }
        }
        $response = ['success_return' => 1];
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
