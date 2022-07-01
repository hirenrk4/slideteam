<?php
namespace Vgroup65\Testimonial\Block\Adminhtml\Configuration\Edit\Tab;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Vgroup65\Testimonial\Model\System\Config\Status;
use Magento\Store\Model\System\Store;
 
class Info extends Generic implements TabInterface
{
 
    protected $_newsStatus;
    
    protected $_systemStore;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Status $newsStatus,
        Store $systemStore, 
        array $data = []
    ) {
        $this->_newsStatus = $newsStatus;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('testimonial_config');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('testimonial_');
        $form->setFieldNameSuffix('testimonial');
        
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General')]
        );
        if ($model->getId()) {
            $fieldset->addField(
                'configuration_id',
                'hidden',
                ['name' => 'configuration_id']
            );
        }
        
        $fieldset->addField(
            'display_type',
            'select',
            [
                'name'        => 'display_type',
                'label'    => __('Display Type'),
                'note'     => 'Select display type to show testimonials on site.',
                'options'  => ['list' => __('List') , 'grid' => __('Grid')]
            ]
        );
         
        
        $fieldset->addField(
            'no_of_testimonial',
            'text',
            [
                'name'      => 'no_of_testimonial',
                'label'     =>__('Testimonial To Display In Widget'),
                'required'  => true,
                'note' => 'Select number of testimonials to display in widget.',
                'class' => 'validate-number'
            ]
        );

        $fieldset->addField(
            'auto_rotate',
            'select',
            [
                'name'      => 'auto_rotate',
                'label'     =>__('Auto Rotation In Widget'),
                'note' => 'Select auto rotation in widget.',
                'options'  => ['1' => __('Yes') , '0' => __('No')]
            ]
        );
        $fieldset->addField(
            'top_menu_link',
            'text',
            [
                'name'      => 'top_menu_link',
                'label'     =>__('Display Testimonial Link in Top Navigation'),
                'note'      =>__('Set testimonial on the top nevigation.'),
                'required' => true
            ]
        );
        
        $fieldset->addField(
            'display_top_menu',
            'select',
            [
                'name'      => 'display_top_menu',
                'label'     =>__('Display Testimonial Link in Top Navigation'),
                'options'  => ['1' => __('Yes') , '0' => __('No')]
            ]
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);
 
        return parent::_prepareForm();
    }
 
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Testimonial Info');
    }
 
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Testimonial Info');
    }
 
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }
 
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}