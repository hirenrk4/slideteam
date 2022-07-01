<?php
namespace Tatva\Resume\Controller\Index;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_resultPageFactory;
    
    public function __construct(
        Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        parent::__construct($context);
    }

    public function getcategoryById($categoryId)
    {
        return $this->catalogCategoryFactory->create()->load($categoryId);
    }
	
	public function execute()
    {
        //$this->_view->loadLayout();
        //$this->_view->renderLayout();
		//$this->_view->loadLayout();
        $resultPage = $this->_resultPageFactory->create();

        $categoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $categoryObj = $this->getcategoryById($categoryId);

        $metatitle = $categoryObj->getMetaTitle();
        $metakeywords = $categoryObj->getMetaKeywords();
        $metadescription = $categoryObj->getMetaDescription();


        $resultPage->getConfig()->getTitle()->set($metatitle);
        $resultPage->getConfig()->setDescription($metadescription);
        $resultPage->getConfig()->setKeywords($metakeywords);

        //$resultPage->getConfig()->getTitle()->set('Resumes');
		return $resultPage;
    }
}
