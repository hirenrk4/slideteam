<?php

namespace Tatva\Notification\Model\Config\Source;

class NotificationType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
          ['value' => 0, 'label' => __('Product')], 
          ['value' => 1, 'label' => __('Blog')],
          ['value' => 2, 'label' => __('Custom')]
        ];
    }
}