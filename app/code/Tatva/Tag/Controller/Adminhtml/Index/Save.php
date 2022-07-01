<?php
namespace Tatva\Tag\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\ResourceConnection;

class Save extends \Magento\Framework\App\Action\Action
{

  protected $resultPageFactory = false;
  public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Tatva\Tag\Model\Tag $tagModel,
    \Magento\Store\Model\StoreManagerInterface $storemanager,
    ResourceConnection  $resource
    ) {
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->_tagModel = $tagModel;
    $this->_storemanager = $storemanager;
    $this->_resource = $resource;
  }
  public function execute() 
  {  
     //  $refererUrl=$this->_redirect->getRefererUrl();
        
   $post = (array) $this->getRequest()->getPost('Tag');
   $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
   $storeId = $this->_storemanager->getStore()->getStoreId();
   $resultRedirect = $this->resultRedirectFactory->create();
   if (!empty($post)) {
    try {
     $model = $this->_tagModel;
     $id = isset($post['tag_id'])?$post['tag_id']:0;
      $data['name']               = trim($post['name']);
      $data['status']             = $post['status'];
      $data['base_popularity']    = (isset($post['base_popularity'])) ? $post['base_popularity'] : 0;
      $data['first_store_id']              = isset($post['store_id'])&& $post['store_id']!="" ?$post['store_id']: $storeId;
      $data['description']        = $post['description'];

     if($id >0){
      $model->load($id);
      $model->addData($data);
      $model->save();
      $this->messageManager->addSuccessMessage("Tag was successfully updated");
    }
    else{
       $model->setData($data);

      $model->save();
        $tag_id = $connection->lastInsertId();
      $sql_insert = "insert into tag_properties(tag_id,store_id,base_popularity) values ('$tag_id','$storeId','$data[base_popularity]')";
      $connection->query($sql_insert);
      $this->messageManager->addSuccessMessage("Tag was successfully saved");
                                //$this->_redirect('booking/index/booking');
    }

    if ($this->getRequest()->getParam("back")) {
      $this->_redirect("*/*/edit",['id' => $model->getId(), '_current' => true]);
      return;
    }
    return $resultRedirect->setPath('*/*/');
  } 
  catch (Exception $e) {
   $this->messageManager->addError(
    __('We can\’t process your request right now. Sorry, that\’s all we know.')
    );
 }  
 return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('tag_id')]);

}


return $resultRedirect->setPath('*/*/');
}

}