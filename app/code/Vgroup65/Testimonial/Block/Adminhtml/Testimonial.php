<?php
namespace Vgroup65\Testimonial\Block\Adminhtml;
use Magento\Backend\Block\Widget\Grid\Container;
class Testimonial extends Container
{
   protected function _construct()
    {
        $this->_controller = 'adminhtml_testimonial';
        $this->_blockGroup = 'Vgroup65_Testimonial';
        $this->_headerText = __('Testimonial list');
        $this->_addButtonLabel = __('Add New');
        $this->buttonList->add('category',['label' => __('Configuration') ,
                               'onclick' => 'setLocation(\'' . $this->getUrl('testimonials/configuration/index') . '\')']);
        $this->buttonList->add('export',['label' => __('Export') ,
                               'onclick' => 'setLocation(\'' . $this->getUrl('testimonials/export/index') . '\')']);
        $this->buttonList->add('import',['label' => __('Import') ,
                               'onclick' => 'setLocation(\'' . $this->getUrl('testimonials/import/index') . '\')']);
        parent::_construct();
    }
}