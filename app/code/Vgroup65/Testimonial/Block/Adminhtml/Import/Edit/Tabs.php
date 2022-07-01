<?php
namespace Vgroup65\Testimonial\Block\Adminhtml\Import\Edit;
 
use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Testimonial Import'));
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Testimonial Import'));
    }
 
    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'testimonial_info',
            [
                'label' => __('Import'),
                'title' => __('Import'),
                'content' => $this->getLayout()->createBlock(
                    'Vgroup65\Testimonial\Block\Adminhtml\Import\Edit\Tab\Info'
                )->toHtml(),
                'active' => true
            ]
        );

 
        return parent::_beforeToHtml();
    }
}