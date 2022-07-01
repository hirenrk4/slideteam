<?php

namespace Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribeplan;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'subscription_id';
    protected function _construct()
    {
        $this->_init(
            'Tatva\Unsubscribeuser\Model\Unsubscribeplan',
            'Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribeplan'
        );
    }
}