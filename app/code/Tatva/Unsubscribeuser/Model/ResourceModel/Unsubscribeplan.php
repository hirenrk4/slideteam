<?php

namespace Tatva\Unsubscribeuser\Model\ResourceModel;

class Unsubscribeplan extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('tatva_unsubscribe_user', 'subscription_id');
    }
}