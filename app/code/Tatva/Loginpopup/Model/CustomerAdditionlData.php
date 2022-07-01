<?php

namespace Tatva\Loginpopup\Model;


class CustomerAdditionlData extends \Magento\Framework\Model\AbstractModel 
{

    const CACHE_TAG = 'customer_additional_data';

    protected $_cacheTag = 'customer_additional_data';
    protected $_eventPrefix = 'customer_additional_data';

    protected function _construct()
    {
        $this->_init('Tatva\Loginpopup\Model\ResourceModel\CustomerAdditionlData');
    }

}
