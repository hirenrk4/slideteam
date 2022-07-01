<?php

namespace Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribeuser;
 
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @param EntityFactoryInterface $entityFactory,
     * @param LoggerInterface        $logger,
     * @param FetchStrategyInterface $fetchStrategy,
     * @param ManagerInterface       $eventManager,
     * @param StoreManagerInterface  $storeManager,
     * @param AdapterInterface       $connection,
     * @param AbstractDb             $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_init('Tatva\Unsubscribeuser\Model\Unsubscribeuser',
            'Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribeuser');
        //Class naming structure 
        // 'NameSpace\ModuleName\Model\ModelName', 'NameSpace\ModuleName\Model\ResourceModel\ModelName'
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }
    
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
                     ['customer_entity'],
                     'main_table.customer_id  = customer_entity.entity_id',
                     ['customer_entity.email','customer_entity.firstname','customer_entity.lastname ']
                    );

        $this->getSelect()->join(
                     ['tatva_unsubscribe_user'],
                     'main_table.subscription_history_id  = tatva_unsubscribe_user.subscription_id',
                     ['tatva_unsubscribe_user.status','tatva_unsubscribe_user.reason','tatva_unsubscribe_user.subscription_id','tatva_unsubscribe_user.backend_comment','tatva_unsubscribe_user.id as unsubid','tatva_unsubscribe_user.unsubscribe_date'])
                    // )->order(array('main_table.unsubscribe_order DESC', 'main_table.subscription_history_id DESC')
                    ->order(new \Zend_Db_Expr("FIELD(tatva_unsubscribe_user.status,'Unsubscribed','Removed From Queue','pending') DESC"))
                    ->order(array('main_table.unsubscribe_order DESC'));
        

        $this->getSelect()->where("customer_entity.entity_id IS NOT NULL");
        //$this->getSelect()->where("main_table.unsubscribe_order != 0");
    }
}