<?php

namespace Tatva\Notification\Model\Config\Source;

class CustomerType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
          ['value' => 0, 'label' => __('All')], 
          ['value' => 1, 'label' => __('Free')],
          ['value' => 2, 'label' => __('Paid')]
        ];
    }
}