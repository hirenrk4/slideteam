<?php

namespace Tatva\Questionnaire\Model\ResourceModel\Questionnaire\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult {


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

}