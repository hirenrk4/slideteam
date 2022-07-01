<?php
namespace Tatva\Deleteaccount\Block;

class Updatecoupon extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;
    protected $_dateFactory; 

    public function __construct
    (
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory  $collection,
        \Tatva\CustomAttribute\Model\ResourceModel\CustomAttribute\CollectionFactory $couponcollection,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory, 
        array $data = []
    )
    {
        parent::__construct($context,$data);
        $this->customerSession = $customerSession;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->collection = $collection;
        $this->couponcollection = $couponcollection;
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }

    public function getPopupcoupon()
    {
        $customer_id = $this->getCustomerId();
        $date = $this->_dateFactory->create()->gmtDate("Y-m-d");

        $collection = $this->couponcollection->create();
        $collection->getSelect()->joinLeft(array('sr'=>'salesrule'),'sr.rule_id=main_table.sales_rule_id',array('sr.rule_id','sr.to_date'));
        $collection->addFieldToFilter('main_table.customer_id',$customer_id);
        $collection->addFieldToFilter('sr.to_date',array('gteq'=>$date));
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('main_table.coupon_code');
        foreach ($collection as $item) 
        {
                $coupon_code = $item['coupon_code'];
        }
        return $coupon_code;
    }
}