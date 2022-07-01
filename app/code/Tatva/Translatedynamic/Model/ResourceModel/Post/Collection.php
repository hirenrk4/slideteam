<?php
namespace Tatva\Translatedynamic\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init('Tatva\Translatedynamic\Model\Post','Tatva\Translatedynamic\Model\ResourceModel\Post');
    }
}