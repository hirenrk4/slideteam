<?php

namespace Tatva\Couponcode\Model\ResourceModel\CouponRating;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'tatva_coupon_rating_info';
	protected $_eventObject = 'coupon_rating_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Tatva\Couponcode\Model\CouponRating', 'Tatva\Couponcode\Model\ResourceModel\CouponRating');
	}

}
