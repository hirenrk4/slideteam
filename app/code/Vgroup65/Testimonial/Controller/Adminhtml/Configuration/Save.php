<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Configuration;

use Vgroup65\Testimonial\Controller\Adminhtml\Configuration;
class Save extends Configuration{
    public function execute() {
        $formData = $this->getRequest()->getParam('testimonial');
        if(isset($formData)):
            try{
            $configModel = $this->_configurationFactory->create();
            $configModel->load($formData['configuration_id']);
            $configModel->setData($formData);  
            $configModel->save();
            
                $this->messageManager->addSuccess(__('Configuration updated successfully'));
                $this->_redirect('*/*/index');
                return;
            }
            catch(\Exception $e){
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/index');
                return;
            }
        endif;
    }
}
