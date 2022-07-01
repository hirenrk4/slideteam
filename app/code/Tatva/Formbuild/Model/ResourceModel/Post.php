<?php
namespace Tatva\Formbuild\Model\ResourceModel;

class Post extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('form_data','form_id');
    }
}