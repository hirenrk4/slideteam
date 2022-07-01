<?php

/**
 * ZohoCrm Resource Collection
 */
namespace Tatva\ZohoCrm\Model\ResourceModel\ZohoCustomerTracking;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tatva\ZohoCrm\Model\ZohoCustomerTracking', 'Tatva\ZohoCrm\Model\ResourceModel\ZohoCustomerTracking');
    }
}
