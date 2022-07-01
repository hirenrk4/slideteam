<?php

namespace Tatva\Unsubscribeuser\Controller\Adminhtml\Items;

class Index extends \Tatva\Unsubscribeuser\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Tatva_Unsubscribeuser::unsubuser');
        $resultPage->getConfig()->getTitle()->prepend(__('Unsubscribe Items'));
        $resultPage->addBreadcrumb(__('Unsubscribe'), __('Unsubscribe'));
        $resultPage->addBreadcrumb(__('Items'), __('Items'));
        return $resultPage;
    }
}