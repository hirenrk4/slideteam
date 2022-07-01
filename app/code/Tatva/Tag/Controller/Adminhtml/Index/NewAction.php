<?php

namespace Tatva\Tag\Controller\Adminhtml\Index;

class NewAction extends \Magento\Backend\App\Action
{

    protected $resultPageFactory = false;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory)
    {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
         $url = $this->getUrl('tag/index/edit');
         $resultRedirect->setUrl($url);
        return $resultRedirect;
    }

}
