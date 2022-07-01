<?php

namespace Tatva\Customer\Controller\Adminhtml\Killedsessions;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory = false;
    protected $jsonFactory;
    private $dataResourceModel;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $jsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Customer\Model\KilledsesssionsFactory $killedSessions
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->killedSessions = $killedSessions;
    }

    public function execute() {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax'))
        {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems))
            {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            }
            else
            {
                foreach (array_keys($postItems) as $modelid)
                {
                    try {
                    $model = $this->killedSessions->create();                      
                        $collection = $model->getCollection()->addFieldToFilter("id",array('eq'=>$modelid));                       
                        
                        foreach($collection as $row)
                        {                           
                            $model = $model->load($row->getId());   
                            $model->setData(array_merge($model->getData(), $postItems[$modelid]));
                            $model->save();
                        }
                    }
                    catch (\Exception $e)
                    {
                        $messages[] = "[Error : {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }
 
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error]);  
    }

}
