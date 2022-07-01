<?php
namespace Tatva\Couponcode\Block\Coupon;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class Lists extends \Magento\Framework\View\Element\Template
{
protected $ruleFactory;

   public function __construct(
      \Magento\Customer\Model\Session $customerSession,
      \Tatva\Couponcode\Model\CouponRating $couponrating,
      \Magento\Catalog\Block\Product\Context $context,
      \Magento\SalesRule\Model\RuleFactory $ruleFactory,
      array $data = []
   )
    {
      $this->ruleFactory = $ruleFactory->create();
      $this->_customerSession = $customerSession;
      $this->couponrating  = $couponrating;
      parent::__construct(
         $context,
         $data
     );
    }
    public function getCurrentCoupons()
    {
      $collection = $this->ruleFactory->getCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('display_on_frontend', 1);
      $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
      $collection->getSelect()->columns(array('main_table.to_date','main_table.is_active','main_table.display_on_frontend','main_table.deal_of_the_day','main_table.discount_amount','rule_coupons.code','main_table.description'));
      return  $collection;
    }

    public function getActiveCoupons(){
      $todayDate = date('Y-m-d', time());
      $collection = $this->getCurrentCoupons();
      $collection->addFieldToFilter('to_date', array('gteq' => $todayDate));
      return  $collection;
    }

    public function getExpiredCoupons(){
      $todayDate = date('Y-m-d', time());
      $collection = $this->getCurrentCoupons();
      $collection->addFieldToFilter('to_date', array('lteq' => $todayDate));
      return  $collection;
    }

    public function isCustomerLogin()
    {
        return $this->_customerSession->isLoggedIn();
    }

    public function getCustomerCouponData($coupon_id,$rating_status)
    {
      $coupon = $this->couponrating->getCollection()
                     ->addFieldToFilter("coupon_id", $coupon_id)
                     ->addFieldToFilter("rating_action", $rating_status)
                     ->addFieldToFilter("customer_id", $this->_customerSession->getCustomerId());
      return $coupon;                     
    }

    public function getCouponData($coupon_id,$rating_status)
    {
      $coupon = $this->couponrating->getCollection()
                     ->addFieldToFilter("coupon_id", $coupon_id)
                     ->addFieldToFilter("rating_action", $rating_status);
      return $coupon;                     
    }
}