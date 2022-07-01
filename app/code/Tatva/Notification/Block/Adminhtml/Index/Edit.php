<?php
namespace Tatva\Notification\Block\Adminhtml\Index;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {

        $this->_objectId = 'notification_id';
        $this->_blockGroup = 'Tatva_Notification';
        $this->_controller = 'adminhtml_index';

        parent::_construct();

        if ($this->_isAllowedAction('Tatva_Notification::view')) {
            $this->buttonList->update('save', 'label', __('Save'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Tatva_Notification::delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Notification'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('notification_form')->getId()) {
            return __("Edit Rule '%1'", $this->escapeHtml($this->coreRegistry->registry('notification_form')->getTitle()));
        } else {
            return __('New Notification');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('notification/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
