<?php

namespace Tatva\Couponcode\Model;

class CouponRating extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'tatva_coupon_rating_info';

	protected $_cacheTag = 'tatva_coupon_rating_info';

	protected $_eventPrefix = 'tatva_coupon_rating_info';

	protected function _construct()
	{
		$this->_init('Tatva\Couponcode\Model\ResourceModel\CouponRating');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}