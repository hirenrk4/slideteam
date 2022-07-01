<?php
namespace Tatva\Translate\Model\ResourceModel;

class Post extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('multilanguage_data','entity_id');
    }
}