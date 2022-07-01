<?php
namespace Tatva\CustomAttribute\Observer;

use Magento\Framework\ObjectManager\ObjectManager;

class CouponcodeOrderObserver implements \Magento\Framework\Event\ObserverInterface{

    protected $_orderFactory;
    protected $salesrulecollection;    
    
    public function __construct
    (
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $salesrulecollection,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->salesrulecollection = $salesrulecollection;      
        $this->_orderFactory = $orderFactory;
    }

	public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order_ids  = $observer->getEvent()->getOrderIds();
        $order_id   = $order_ids[0];

        //Loading order details
        $orderModel         = $this->_orderFactory->create();
        $order              = $orderModel->load($order_id);
        $shipping_method    = $order->getShippingMethod();
        $customer_id        = $order->getCustomerId();
        $coupon             = $order->getCouponCode();

        $collection = $this->salesrulecollection->create();
        $collection->getSelect()->joinInner(array('src'=>'coupon_customer_relation'),'src.sales_rule_id=main_table.rule_id',array('src.sales_rule_id','src.coupon_code'));
        $collection->addFieldToFilter('src.coupon_code',$coupon);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('main_table.rule_id');
        foreach ($collection as $item) 
        {
                $item->delete();
        }
    }
}