<?php
namespace Tco\Checkout\Model\ResourceModel;


class Ins extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
        ) {
        parent::__construct(
            $context,
            $connectionName
            );
    }

    public function _construct()
    {    
        // Note that the metadescription_id refers to the key field in your database table.
        $this->_init('2checkout_ins', 'id');
    }
}