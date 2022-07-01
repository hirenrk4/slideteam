<?php

namespace Tatva\Formbuild\Controller\Index;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;
    private $url;

    public function __construct(
        Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Formbuild\Model\PostFactory $postFactory,
        \Magento\Framework\UrlInterface $url
    )
    {
        $this->_postFactory = $postFactory;
        $this->url = $url;
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    public function getFormId()
    {   
        return $this->getRequest()->getParam('form_id');
    }

    public function execute()
    {
        /*echo "string";
        exit();*/
        
        $collection = $this->_postFactory->create()->getCollection()->addFieldToFilter('form_id', $this->getFormId());
        foreach ($collection->getData() as $data){
            $status = $data['status'];
        }
        
        if ($status == '1') {
            
            $resultPage = $this->_resultPageFactory->create();
            return $resultPage;
        }

        else{

            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $norouteUrl = $this->url->getUrl('noroute');
            $result = $resultRedirect->setUrl($norouteUrl);
            return $result;
        }
    }

}
