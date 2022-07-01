<?php
namespace Vgroup65\Testimonial\Block\Adminhtml\Testimonial;
 
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
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_testimonial';
        $this->_blockGroup = 'Vgroup65_Testimonial';
 
        parent::_construct();
 
        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete'));
    }
 
    /**
     * Retrieve text for header element depending on loaded news
     * 
     * @return string
     */
    public function getHeaderText()
    {
        $testimonialRegistry = $this->_coreRegistry->registry('testimonial_list');
        if ($testimonialRegistry->getId()) {
            $testimonialTitle = $this->escapeHtml($testimonialRegistry->getTitle());
            return __("Edit Testimonial '%1'", $testimonialTitle);
        } else {
            return __('Add New');
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