<?php
namespace Tatva\Couponcode\Model\ResourceModel;


class CouponRating extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('tatva_coupon_rating_info', 'id');
	}
	
}