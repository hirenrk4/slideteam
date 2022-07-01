<?php

/**
 * Customproductreview Resource Collection
 */
namespace Tatva\Customproductreview\Model\ResourceModel\Customproductreview;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'review_id';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tatva\Customproductreview\Model\Customproductreview', 'Tatva\Customproductreview\Model\ResourceModel\Customproductreview');
    }
}
