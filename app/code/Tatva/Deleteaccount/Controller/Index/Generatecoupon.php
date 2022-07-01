<?php
namespace Tatva\Deleteaccount\Controller\Index;

class Generatecoupon extends \Magento\Framework\App\Action\Action
{
    protected $_customerSession;
    protected $_dateFactory; 

    public function __construct
    (
        \Magento\Customer\Model\Session $customerSession, 
        \Tatva\Deleteaccount\Model\Coupon $coupon,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Tatva\CustomAttribute\Model\ResourceModel\CustomAttribute\CollectionFactory $couponcollection, 
        \Magento\Framework\App\Action\Context $context
    )
    {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->couponcollection = $couponcollection;
        $this->coupon=$coupon;
    }

    public function execute()
    {
        if (!empty($_POST)) 
        {
            $loginCustomerid = $_POST['data'];
        }

        $date = $this->_dateFactory->create()->gmtDate("Y-m-d");
        $collection = $this->couponcollection->create();
        $collection->getSelect()->joinLeft(array('sr'=>'salesrule'),'sr.rule_id=main_table.sales_rule_id',array('sr.rule_id','sr.to_date'));
        $collection->addFieldToFilter('main_table.customer_id',$loginCustomerid);
        $collection->addFieldToFilter('sr.to_date',array('gteq'=>$date));
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('main_table.coupon_code');
        $size = $collection->getSize();

        if($size == 0)
        {
            $this->coupon->createRule($loginCustomerid);
        }
    }

}