<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Configuration;

use Vgroup65\Testimonial\Controller\Adminhtml\Configuration;
class Index extends Configuration{
    
    public function execute(){
        
        $configurationModel = $this->_configurationFactory->create();
        $testimonialConfigurationId = 1;
            $configurationModel->load($testimonialConfigurationId);
            if(!$configurationModel->getId()){
                $this->_messageManager->addError('This id is no longer exist');
                $this->_redirect('*/*/');
                return;
            }

       
        $this->_coreRegistry->register('testimonial_config', $configurationModel);
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Vgroup65_Testimonial::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Configuration'));
        return $resultPage;
    }
    
}