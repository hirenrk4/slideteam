<?php
namespace Tatva\RecurringOrders\Model\ResourceModel;

class Recurringorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('recurring_orders','id');
    }
}