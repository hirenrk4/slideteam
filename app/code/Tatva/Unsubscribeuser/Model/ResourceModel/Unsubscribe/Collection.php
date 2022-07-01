<?php

namespace Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribe;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(
            'Tatva\Unsubscribeuser\Model\Unsubscribe',
            'Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribe'
        );
    }
}