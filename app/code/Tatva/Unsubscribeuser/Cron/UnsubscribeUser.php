<?php
namespace Tatva\Unsubscribeuser\Cron;
use Tatva\Unsubscribeuser\Model\UnsubscribeFactory;

class UnsubscribeUser
{
    protected $_scopeConfig;
    protected $_unsubscribeFactory;
    protected $date;
    protected $curl;
    protected $_customerRepositoryInterface;


    public function __construct(       
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        UnsubscribeFactory $UnsubscribeFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Tatva\Subscription\Model\SubscriptionFactory $subscription
    ) {     
        $this->_scopeConfig  = $scopeConfig; 
        $this->date = $date;
        $this->_unsubscribeFactory = $UnsubscribeFactory;
        $this->curl = $curl;
        $this->_storeManager = $storeManager;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_subscription = $subscription;
    }

    public function execute()
    {
        $delayedTime = $this->_scopeConfig->getValue('unsubscription_options/unsubscription/unsubscription_delay_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        //$delayedTime = 1;
        $hours = '-'.$delayedTime.' hour';
        $filterDate = $this->date->gmtDate('Y-m-d H:i:s');
        $previousTime = date("Y-m-d H:i:s",strtotime($filterDate.$hours));
        
        $model = $this->_unsubscribeFactory->create();
        //$resultPageData = $model->load('*');
        $collection = $model->getCollection();
        $collection->getSelect()->joinLeft(array("tbl_customer"=>"customer_entity"),"tbl_customer.entity_id = main_table.customer_id",array("tbl_customer.entity_id as customer_entity_id"));
        $collection->addFieldToFilter(
            'unsubscribe_date',
            array(
                'lteq'=>$previousTime
            )
        );
        $collection->addFieldToFilter('status',array('eq'=> 'pending'));
        
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        
        foreach ($collection as $unsub_item) {
            
            if(empty($unsub_item['customer_entity_id']))
            {
                $model = $this->_unsubscribeFactory->create();
                $model->load($unsub_item['id'])->delete();
                $this->_subscription->create()->load($unsub_item['subscription_id'])->setUnsubscribeOrder(0)->save();
                continue;
            }
            $customer = $this->_customerRepositoryInterface->getById($unsub_item['customer_id']);
            
            $uri = $baseUrl.'paypalrec/unsubscribe/?unsub_crondata=1&subscription_id='.$unsub_item['subscription_id'].'&customer_id='.$unsub_item['customer_id'].'&customer_email='.$customer->getEmail();
            
            $result = $this->curl->get($uri);
    
            $id_post_update = $unsub_item['id'];
            $postUpdate = $model->load($id_post_update);
            $postUpdate->setStatus('Unsubscribed');
            $postUpdate->save();

            $this->_subscription->create()->load($unsub_item['subscription_id'])->setUnsubscribeOrder(0)->save();
        }
        
    }
}