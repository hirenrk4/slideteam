<?php

namespace Tatva\Notification\Model\Config\Source;

class PaidDuration implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
          ['value' => 0, 'label' => __('Monthly')], 
          ['value' => 1, 'label' => __('Semi Annual')],
          ['value' => 2, 'label' => __('Annual')],
          ['value' => 3, 'label' => __('Annual + Custom Design')],
          ['value' => 4, 'label' => __('None')]
        ];
    }
}