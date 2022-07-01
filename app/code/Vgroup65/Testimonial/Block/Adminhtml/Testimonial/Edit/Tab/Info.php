<?php
namespace Vgroup65\Testimonial\Block\Adminhtml\Testimonial\Edit\Tab;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Vgroup65\Testimonial\Model\System\Config\Status;
use Magento\Store\Model\System\Store;
 
class Info extends Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    protected $_status;
    
    protected $_systemStore;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $status,
        Store $systemStore,   
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_status = $status;
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
       /** @var $model \Tutorial\SimpleNews\Model\News */
        $model = $this->_coreRegistry->registry('testimonial_list');

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
                'testimonial_id',
                'hidden',
                ['name' => 'testimonial_id']
            );
        }
        
        $fieldset->addField(
            'first_name',
            'text',
            [
                'name'        => 'first_name',
                'label'    => __('First Name'),
                'required'     => true,
                'note'   => "Maximum 50 characters",
                'maxlength' => 50
            ]
        );
        
        $fieldset->addField(
            'last_name',
            'text',
            [
                'name'        => 'last_name',
                'label'    => __('Last Name'),
                'required'     => true,
                'note'   => "Maximum 50 characters",
                'maxlength' => 50
            ]
        );
        $fieldset->addField(
            'gender',
            'select',
            [
                'name'        => 'gender',
                'label'    => __('Gender'),
                'options'   => $this->_status->toOptionGender()
            ]
        );
        $fieldset->addField(
            'age',
            'text',
            [
                'name'        => 'age',
                'label'    => __('Age'),
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'designation',
            'text',
            [
                'name'        => 'designation',
                'label'    => __('Designation'),
                'required'     => true,
                'note'   => "Maximum 50 characters",
                'maxlength' => 50
            ]
        );
        $fieldset->addField(
            'company',
            'text',
            [
                'name'        => 'company',
                'label'    => __('Company'),
                'required'     => true,
                'note'   => "Maximum 75 characters",
                'maxlength' => 75
            ]
        );
        
        $fieldset->addType('image', 'Vgroup65\Testimonial\Block\Adminhtml\Helper\Image\Required');
        $fieldset->addField(
            'image',
            'image',
            [
                'name'        => 'image',
                'label'    => __('Image'),
                'note'  => 'Minimum Image size should be 200*200px'
            ]
        );

        $fieldset->addField(
            'testimonial',
            'textarea',
            [
                'name'        => 'testimonial',
                'label'    => __('Testimonial'),
                'required'     => true,
                'note'   => "Maximum 1000 characters",
                'maxlength' => 1000
            ]
        );
        $fieldset->addField(
            'website',
            'text',
            [
                'name'        => 'website',
                'label'    => __('Website'),
                'class' => 'validate-url'
            ]
        );
        
        $fieldset->addField(
            'address',
            'textarea',
            [
                'name'        => 'address',
                'label'    => __('Address'),
                'note'   => "Maximum 40 characters",
                'maxlength' => 40
            ]
        );
        
        $fieldset->addField(
            'city',
            'text',
            [
                'name'        => 'city',
                'label'    => __('City'),
                'note'   => "Maximum 50 characters",
                'maxlength' => 50
            ]
        );
        
        $fieldset->addField(
            'state',
            'select',
            [
                'name'        => 'state',
                'label'    => __('State'),
                'options'  => $this->_status->toOptionStates()
            ]
        );
        
        $fieldset->addField(
            'status',
            'select',
            [
                'name'      => 'status',
                'label'     => __('Status'),
                'options'   => $this->_status->toOptionArray(),
                'required'     => true
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