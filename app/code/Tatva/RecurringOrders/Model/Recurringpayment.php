<?php
namespace Tatva\RecurringOrders\Model;

class Recurringpayment extends \Magento\Framework\Model\AbstractModel
{
	protected $_recurringorderFactory;
	protected $_subscriptionhistoryFactory;
	protected $_paymentFactory;

    public function __construct
    (
    	\Tatva\RecurringOrders\Model\RecurringorderFactory $recurringorderFactory,
    	\Tatva\Subscription\Model\SubscriptionFactory $subscriptionhistoryFactory,
    	\Magento\Sales\Model\Order\PaymentFactory $paymentFactory
    )
    {
    	$this->_paymentFactory = $paymentFactory;
    	$this->_recurringorderFactory = $recurringorderFactory;
    	$this->_subscriptionhistoryFactory = $subscriptionhistoryFactory;
    }

    public function Recurringpayment($order_id)
    {	
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/recurringorders.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($order_id.'<br>');

    	$collection = $this->_subscriptionhistoryFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(array('so'=>'sales_order'),'so.increment_id=main_table.increment_id',array('so.increment_id','so.entity_id'));
        $collection->addFieldToFilter('so.entity_id',$order_id);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array('so.base_grand_total','so.customer_email','subscription_start_date','renew_date'));
        //->where('so.entity_id = '.$order_id)
        //->reset(\Zend_Db_Select::COLUMNS)
        //->columns(array('so.base_grand_total','so.customer_email','subscription_start_date','renew_date','subscription_history_id'));
        /*->order('subscription_history_id DESC')
        ->limit(1);*/


        foreach($collection as $order)
        {         
        	$customer_email = $order->getCustomerEmail();   
            $created_at = $order->getSubscriptionStartDate();
            $renew_date = $order->getRenewDate();
            $billing_amount = $order->getBaseGrandTotal();
        }

        if(empty($customer_email))
        {
            $customer_email = "";    
        }
        
        $collection1 = $this->_paymentFactory->create()->getCollection();
        $collection1->addFieldToFilter('parent_id',$order_id);

        foreach($collection1 as $payment)
        {            
            $pay_method = $payment->getMethod();
        }

    	$model = $this->_recurringorderFactory->create();
		$data = array('order_id' => $order_id, 'cust_email' => $customer_email, 'recu_amount' => $billing_amount, 'recu_datetime' => $created_at, 'pay_method' => $pay_method, 'nextrecu_datetime' => $renew_date);
		$model->setData($data);
		$saveData = $model->save();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/recurringordersdatasaved.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('order: '.$order_id.' email : '.$customer_email.'<br> method: '.$pay_method);
    }
}