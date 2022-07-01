<?php
namespace Tatva\Metadescription\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Framework\App\Action\Action
{

  protected $resultPageFactory = false;
  public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Tatva\Metadescription\Model\Metadescription $metadescription,
    \Magento\Framework\Message\ManagerInterface $messageManager,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->metadescription = $metadescription;
    $this->messageManager = $messageManager;
    $this->timezone = $timezone;
  }
  public function execute() 
  {
    $max_count = '';
    $max_count_product = '';
    $temp = '';
    $temp_product = '';
    $metadescription_collection_count_product = $this->metadescription->getCollection();

        // Get Maximum Number of Count from column 'number_of_usage_product'
    $metadescription_collection_count_product->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('MAX(number_of_usage_product) as number_of_usage_product');

    foreach($metadescription_collection_count_product as $c)
    {
      $max_count_product = $c['number_of_usage_product'];
    }

    $post = (array) $this->getRequest()->getPost('Metadescription');
    $resultRedirect = $this->resultRedirectFactory->create();
    $count = 0;
    if (!empty($post)) {
      $modelCollection = $this->metadescription->getCollection();

      try {
        if ($this->metadescription->getCreatedTime() == NULL ||  $this->metadescription->getUpdateTime() == NULL) {
          $this->metadescription->setCreatedTime(time())
          ->setUpdateTime(time());
        } else {
          $this->metadescription->setUpdateTime(time());
        }
        $id = $this->getRequest()->getParam('metadescription_id');

        if(!$id)
        {
          $count = 0;

          $lineLower = strtolower($post['metadescription']);

          foreach($modelCollection as $m)
          {
            $metadescription = $m->getMetadescription();
            $metadescription = strtolower($metadescription);

            if(strcmp($metadescription,$lineLower) == '0')
            {
              $count++;
              break;
            }
          }

          if($count == '0')
          {
            if($max_count_product)
            {
              $temp_product = $max_count_product - 1;
              $this->metadescription->setNumberOfUsageProduct($temp_product);
            }
            else
            {
             $this->metadescription->setNumberOfUsageProduct('0');
           }


           $this->metadescription->load($id);
           $this->metadescription->addData($post);
           $this->metadescription->save();
           // $this->metadescription->setMetadescription($post['metadescription']);
           // $this->metadescription->save();
         }
         else
         {
          $this->messageManager->addError(__('Cannot save, metadescription already exists.'));
          $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
          return;
        }
      }
      else
      {

        $lineLower = strtolower($post['metadescription']);
        foreach($modelCollection as $m)
        {
          if($m->getId() != $id)
          {
            $metadescription = $m->getMetadescription();
            $metadescription = strtolower($metadescription);

            if(strcmp($metadescription,$lineLower) == '0')
            {
              $count++;
              break;
            }
          }
        }
        if($count == '0')
        {
          if($max_count_product)
          {
            $temp_product = $max_count_product - 1;
            $this->metadescription->setNumberOfUsageProduct($temp_product);
          }
          else
          {
           $this->metadescription->setNumberOfUsageProduct('0');
         }
         $this->metadescription->setMetadescription($data['metadescription']);
         $this->metadescription->save();
       }
       else
       {
        $this->messageManager->addError(__('Cannot save, metadescription already exists.'));
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        return;
      }
    }
    $this->messageManager->addSuccess(__('Metadescription was successfully saved'));
    //$this->session->setFormData(false);

    if ($this->getRequest()->getParam('back')) {
      $this->_redirect('*/*/edit', array('id' => $this->metadescription->getId()));
      return;
    }
    $this->_redirect('metadescription/index/index');
    return;
  }
  catch (Exception $e) {
   $this->messageManager->addError(
    __('Cannot save, metadescription already exists.')
    );
 }  
 return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('metadescription_id')]);

}


return $resultRedirect->setPath('metadescription/index/index');
}

}