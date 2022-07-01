<?php
namespace Tatva\Subscription\Model\ResourceModel\Shareanddownloadproducts;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'shareanddownloadproducts_id';
	protected $_eventPrefix = 'Shareanddownloadproducts_subscription_collection';
	protected $_eventObject = 'Shareanddownloadproducts_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
    	$this->_init('Tatva\Subscription\Model\Shareanddownloadproducts', 'Tatva\Subscription\Model\ResourceModel\Shareanddownloadproducts');
    }

}
