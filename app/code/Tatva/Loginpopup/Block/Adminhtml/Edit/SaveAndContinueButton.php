<?php

namespace Tatva\Loginpopup\Block\Adminhtml\edit;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinueButton  implements ButtonProviderInterface
{

    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'saveAndContinueEdit']],
                'form-role' => 'save',
            ],
            'sort_order' => 80,
        ];
    }

}


