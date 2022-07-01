<?php


namespace Tatva\Unsubscribeuser\Controller\Adminhtml\Items;

class InlineEdit extends \Magento\Backend\App\Action
{

    protected $jsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Tatva\Unsubscribeuser\Model\UnsubscribeFactory $unsubscribeUser
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->_unsubscribeFactory = $unsubscribeUser;
    }

    public function execute()
    {
       
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $modelid) {
                    /** @var \Magento\Cms\Model\Block $block */
                    
                    try {
                        
                        $model = $this->_unsubscribeFactory->create();                      
                        $collection = $model->getCollection()->addFieldToFilter("subscription_id",array('eq'=>$modelid));                       
                        
                        foreach($collection as $row)
                        {                           
                            $model = $model->load($row->getId());   
                            $model->setData(array_merge($model->getData(), $postItems[$modelid]));
                            $model->save();
                        }
                        
                    } catch (\Exception $e) {
                        $messages[] = "[unsubusering ID: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}