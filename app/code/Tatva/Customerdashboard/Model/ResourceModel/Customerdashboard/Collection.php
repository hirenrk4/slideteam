<?php

/**
 * Customerdashboard Resource Collection
 */
namespace Tatva\Customerdashboard\Model\ResourceModel\Customerdashboard;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tatva\Customerdashboard\Model\Customerdashboard', 'Tatva\Customerdashboard\Model\ResourceModel\Customerdashboard');
    }
}
