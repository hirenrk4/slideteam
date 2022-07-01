<?php
namespace Vgroup65\Testimonial\Block\Adminhtml\Configuration\Edit;
 
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
        $this->setId('testimonial_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Testimonial Configuration'));
    }
 
    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'news_info',
            [
                'label' => __('Testimonial Info'),
                'title' => __('Testimonial Info'),
                'content' => $this->getLayout()->createBlock(
                    'Vgroup65\Testimonial\Block\Adminhtml\Configuration\Edit\Tab\Info'
                )->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}