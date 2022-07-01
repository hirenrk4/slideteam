<?php
namespace Tatva\Translate\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init('Tatva\Translate\Model\Post','Tatva\Translate\Model\ResourceModel\Post');
    }
}