<?php
namespace Tatva\Formbuild\Controller\Adminhtml\Index;

class Edit extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Tatva_Formbuild::edit';

    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Forms Management')));
        return $resultPage;
    }
}