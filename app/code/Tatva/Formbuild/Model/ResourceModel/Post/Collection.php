<?php
namespace Tatva\Formbuild\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'form_id';

    protected function _construct()
    {
        $this->_init('Tatva\Formbuild\Model\Post','Tatva\Formbuild\Model\ResourceModel\Post');
    }
}