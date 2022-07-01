<?php
namespace Tatva\Metatitle\Controller\Adminhtml\Imports;

/*
 This is Controller for Tatva_Metatitle_Adminhtml_Imports
 Vendor : Tatva
 Module : Metatitle (Generate Metatitle of products)
 Author : Tatva
 Parent Class : Mage_Adminhtml_Controller_Action
*/

 class Index extends \Magento\Backend\App\Action
 {

    /**
     * @var \Tatva\Metatitle\Model\Mysql4\Metatitle\CollectionFactory
     */
    protected $metatitleCollectionFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Tatva\Metatitle\Model\MetatitleFactory
     */
    protected $metatitleMetatitleFactory;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
       // var_dump($this->resultPageFactory);die;
    }
    public function execute()
    {
    //     //var_dump($_REQUEST);die;
        if ($this->getRequest()->getQuery('ajax')) {

            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->prepend((__('Metatitle')));

        return $resultPage;
    }


}