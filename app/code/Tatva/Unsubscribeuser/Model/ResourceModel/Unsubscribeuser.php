<?php

namespace Tatva\Unsubscribeuser\Model\ResourceModel;

class Unsubscribeuser extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('subscription_history', 'subscription_history_id ');
    }
}