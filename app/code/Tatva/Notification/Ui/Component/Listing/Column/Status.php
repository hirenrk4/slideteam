<?php
namespace Tatva\Notification\Ui\Component\Listing\Column;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Disabled'), 'value' => '0'],
            ['label' => __('Enabled'), 'value' => '1'],
        ];
    }
}
