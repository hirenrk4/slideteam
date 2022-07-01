<?php
namespace Tatva\Translate\Model\ResourceModel\Language;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init('Tatva\Translate\Model\Language','Tatva\Translate\Model\ResourceModel\Language');
    }
}