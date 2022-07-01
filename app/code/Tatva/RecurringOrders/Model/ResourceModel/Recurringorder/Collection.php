<?php
namespace Tatva\RecurringOrders\Model\ResourceModel\Recurringorder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Tatva\RecurringOrders\Model\Recurringorder','Tatva\RecurringOrders\Model\ResourceModel\Recurringorder');
    }
}