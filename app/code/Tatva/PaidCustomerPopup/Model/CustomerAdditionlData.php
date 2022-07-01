<?php

namespace Tatva\PaidCustomerPopup\Model;

class CustomerAdditionlData extends \Magento\Framework\Model\AbstractModel 
{

    const CACHE_TAG = 'paid_customer_additional_data';

    protected $_cacheTag = 'paid_customer_additional_data';
    protected $_eventPrefix = 'paid_customer_additional_data';

    protected function _construct()
    {
        $this->_init('Tatva\PaidCustomerPopup\Model\ResourceModel\CustomerAdditionlData');
    }

}
