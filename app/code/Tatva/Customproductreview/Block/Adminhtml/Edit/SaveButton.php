<?php

namespace Tatva\Customproductreview\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData()
    {

        return [
        'label' => __('Save Item'),
        'class' => 'save primary',
        'data_attribute' => [
        'mage-init' => ['button' => ['event' => 'save']],
        'form-role' => 'save',
        ],
        'sort_order' => 90,
        ];
    }
}

