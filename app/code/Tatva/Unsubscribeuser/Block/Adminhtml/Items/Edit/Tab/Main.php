<?php


namespace Tatva\Unsubscribeuser\Block\Adminhtml\Items\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    protected $_wysiwygConfig;
 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $registry, 
        \Magento\Framework\Data\FormFactory $formFactory,  
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, 
        array $data = []
    ) 
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Unsubscribe Item');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Unsubscribe Item');
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

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_tatva_unsubscribeuser_items');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        
        $fieldset->addField(
            'customer_id',
            'text',
            ['name' => 'author', 'label' => __('Customer Id'), 'title' => __('Customer Id'),'disabled' => true]
        );
        $fieldset->addField(
            'subscription_id',
            'text',
            [
                'name' => 'subscription_id',
                'label' => __('Subscription Id'),
                'title' => __('Subscription Id'),
                'disabled' => true
            ]
        );
        $fieldset->addField(
            'reason',
            'text',
            [
                'name' => 'reason',
                'label' => __('Reason'),
                'title' => __('Reason'),
                'disabled' => true
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            ['name' => 'status', 'label' => __('Status'), 'title' => __('Status'),  'options'   => ['pending' => 'Pending','Unsubscribed' => 'Unsubscribed','cancelled' => 'Cancelled',]]
        );
        
        
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
