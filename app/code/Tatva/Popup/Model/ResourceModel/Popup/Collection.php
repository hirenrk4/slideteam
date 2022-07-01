<?php

/**
 * Popup Resource Collection
 */
namespace Tatva\Popup\Model\ResourceModel\Popup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tatva\Popup\Model\Popup', 'Tatva\Popup\Model\ResourceModel\Popup');
    }
}
