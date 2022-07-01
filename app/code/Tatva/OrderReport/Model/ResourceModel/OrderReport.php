<?php
namespace Tatva\OrderReport\Model\ResourceModel;


class OrderReport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
        )
    {
        parent::__construct($context);
    }
    
    protected function _construct()
    {
        $this->_init('subscription_history', 'subscription_history_id');
    }

}