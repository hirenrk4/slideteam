<?php
namespace Tatva\Subscription\Model\ResourceModel\Subscription;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class AdminCustomerSubReport extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected $resourceConnection;
    protected $connection;
    protected $registry;

    public function __construct(
    	\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
    	\Psr\Log\LoggerInterface $logger,
    	\Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
    	\Magento\Framework\Event\ManagerInterface $eventManager,
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
    	\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    	) {
    	$this->_init('Tatva\Subscription\Model\Subscription', 'Tatva\Subscription\Model\ResourceModel\Subscription');
    	parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    	$this->storeManager = $storeManager;
    	$this->_resource = $resource;
    }
    protected function _initSelect()
    {
    	parent::_initSelect();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $model = $objectManager->create('Tatva\Catalog\Model\Productdownloadhistory');
        $customerId = $model->getcustomerID();
        $this->getSelect()->joinLeft(
          ['cp1' => $this->getTable('2checkout_ins')],
          'cp1.id =main_table.two_checkout_message_id OR cp1.id = NULL',
          ['main_table.increment_id','main_table.from_date','main_table.subscription_period','main_table.to_date','main_table.renew_date','main_table.status_success','main_table.customer_id','main_table.paypal_id','main_table.two_checkout_message_id','final_amount'=>'cp1.invoice_usd_amount']
          )->joinLeft(
          ['cp2' => $this->getTable('paypal_result')],
          'main_table.paypal_id = cp2.id OR cp2.id = NULL',
          ['final_amount_paypal'=>'cp2.amount']
          )->joinLeft(
          ['cp3' => $this->getTable('customer_entity')],
          'main_table.parent_customer_id = cp3.entity_id',
          ['parent_email'=>'cp3.email']
          )->joinLeft(
          ['cp4' => $this->getTable('amasty_recurring_payments_subscription')],
          'main_table.customer_id = cp4.customer_id',
          ['final_amount_stripe'=>'cp4.base_grand_total']
          )->where('main_table.customer_id='.$customerId);
          return $this;
      }
      public function getConnection()
      {
          if (!$this->connection) {
             $this->connection = $this->_resource->getConnection('core_write');
         }
         return $this->connection;
     }
 }
