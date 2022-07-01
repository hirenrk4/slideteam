<?php

namespace Tatva\Tag\Model\ResourceModel\Tag\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class PendingTagCollection extends SearchResult {


	public function __construct(
		EntityFactoryInterface $entityFactory, 
		LoggerInterface $logger, 
		FetchStrategyInterface $fetchStrategy, 
		ManagerInterface $eventManager, 
		$mainTable, 
		$resourceModel
		) {
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
	}

	 protected function _initSelect()
     {
     	$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
		$storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$storeId       = $storeManager->getStore()->getStoreId(); 

         $this->getSelect()
         ->from(
            ['main_table' => 'tag'],
            ['*']
        )->
         joinLeft(
		   ['summary'=>$this->getTable('tag_summary')],
		   'main_table.tag_id = summary.tag_id',
		   ['store_id','popularity', 'customers', 'products'])
         ->where('main_table.status='.\Tatva\Tag\Model\Tag::STATUS_PENDING)
           ->where('store_id IN(?)', $storeId);			

        return $this;
    }

}