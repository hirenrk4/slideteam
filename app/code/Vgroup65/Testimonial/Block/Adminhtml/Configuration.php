<?php
namespace Vgroup65\Testimonial\Block\Adminhtml;
use Magento\Backend\Block\Widget\Grid\Container;
class Configuration extends Container
{
   protected function _construct()
    {
        $this->_controller = 'adminhtml_configuration';
        $this->_blockGroup = 'Vgroup65_Testimonial';
        $this->_headerText = __('Testimonial Configuration');
        $this->_addButtonLabel = __('Add New');
        parent::_construct();
    }
}