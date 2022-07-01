<?php
namespace Vgroup65\Testimonial\Block\Adminhtml\Helper\Image;
class Required extends \Magento\Framework\Data\Form\Element\Image
{
    
//    protected function _getDeleteCheckbox()
//    {
//        return '<input type="hidden" name="' . parent::getName() . '[value]" value="' . $this->getValue() . '" />';
//    }
    
    protected function _getUrl()
    {
        if (!filter_var($this->getValue(), FILTER_VALIDATE_URL)):
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->create('Vgroup65\Testimonial\Helper\Data');
            
            if(!empty($this->getValue())):
                return $helper->getBaseUrl().$this->getValue();
            endif;
            
            else:
            return $this->getValue();
        endif;
        
    }
}