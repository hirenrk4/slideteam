<?php
namespace Tatva\Translate\Model\ResourceModel;

class Language extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('languages','entity_id');
    }
}