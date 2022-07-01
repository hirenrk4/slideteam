<?php
namespace Vgroup65\Testimonial\Block\Adminhtml\Import;
 
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
class Edit extends Container
{
   /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
 
    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
 
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_controller = 'adminhtml_import';
        $this->_blockGroup = 'Vgroup65_Testimonial';
 
        parent::_construct();
 
        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->update('back', 'onclick', "setLocation('" . $this->getUrl('testimonials/testimonial/index') . "')");
    }
 
    /**
     * Retrieve text for header element depending on loaded news
     * 
     * @return string
     */
    public function getHeaderText()
    {
        $newsRegistry = $this->_coreRegistry->registry('testimonial_list');
        if ($newsRegistry->getId()) {
            $newsTitle = $this->escapeHtml($newsRegistry->getTitle());
            return __("Edit Testimonial '%1'", $newsTitle);
        } else {
            return __('Add Testimonial');
        }
    }
 
    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            };
        ";
 
        return parent::_prepareLayout();
    }
}