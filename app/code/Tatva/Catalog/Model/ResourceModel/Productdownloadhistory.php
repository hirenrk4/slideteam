<?php
namespace Tatva\Catalog\Model\ResourceModel;


class Productdownloadhistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
        ) {
        parent::__construct(
            $context
            );
    }

    public function _construct()
    {    
        // Note that the subscription_id refers to the key field in your database table.
        $this->_init('productdownload_history', 'download_history_id');
    }
}